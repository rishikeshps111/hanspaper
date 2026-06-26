<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

use App\Enums\Item as ItemEnums;

use App\Services\ItemTransactionService;
use App\Services\ItemService;

use App\Traits\FormatNumber;
use App\Traits\FormatsDateInputs;
use App\Models\Items\Item;
use App\Models\Items\ItemCategory;
use App\Models\Items\ItemTransaction;
use App\Models\Unit;
use App\Models\Tax;
use App\Models\State;
use App\Models\Items\ItemBatchTransaction;
use App\Models\Party\Party;
use App\Services\AccountTransactionService;

use App\Services\PartyTransactionService;
use App\Enums\ItemTransactionUniqueCode;
use App\Models\Currency;
use App\Models\Items\Brand;

class ImportController extends Controller
{
    use FormatsDateInputs;

    use FormatNumber;

    public $reader ;

    public $itemModel;

    public $defaultItemCategory;

    public $dateFormat;

    public $itemService;

    public $accountTransactionService;

    public $itemTransactionService;

    function __construct(Xlsx $reader, Item $itemModel, ItemService $itemService, AccountTransactionService $accountTransactionService, ItemTransactionService $itemTransactionService)
    {
        $this->itemModel  = $itemModel ;
        $this->reader  = $reader ;
        $this->defaultItemCategory  = ItemEnums::DEFAULT_ITEM_CATEGORY->value ;
        $this->dateFormat  = app('company')['date_format'];
        $this->itemService  = $itemService ;
        $this->accountTransactionService  = $accountTransactionService ;
        $this->itemTransactionService  = $itemTransactionService ;
    }

    public function items() : View {
        return view('import.item');
    }

    public function parties() : View {
        return view('import.party');
    }

    /**
     * Import the Excel Sheet Records
     * @return JsonResponse
     * */
    public function importItems(Request $request)
    {
        $file = $request->file('excel_file');

        $spreadsheet = $this->reader->load($file->getPathname());

        // Select the second sheet
        $sheetNumberOne = 0;
        $sheetOne = $spreadsheet->getSheet($sheetNumberOne); // Sheet indices start at 0, so 1 is the second sheet

        $sheetNumberTwo = 1;
        $sheetTwo = $spreadsheet->getSheet($sheetNumberTwo); // Sheet indices start at 0, so 1 is the second sheet

        // Get the data from the second sheet
        $data = $sheetOne->toArray();
        $dataTwo = $sheetTwo->toArray();

        $itemIds = [];

        try{
            DB::beginTransaction();

            // Do something with the data
            $i = 0;
            if(count($data) <= 1){
                throw new \Exception(__('app.records_not_found'));
            }
            foreach ($data as $row) {
                $i++;
                if($i === 1){
                    continue;
                }

                $itemName       = trim($row[0]);//required
                $description    = trim($row[1]);
                $itemType       = 'Product';//required, "Product" or "Service"
                $itemCode       = trim($row[2]);//required
                $category       = trim($row[3]);
                $brand          = trim($row[4]);

                $recordDetails = "Sheet:".$sheetNumberOne.", Row:".($i);

                $validator = Validator::make([
                        'itemName'  => $itemName,
                        'itemType'  => $itemType,
                    ],[
                        'itemName' => ['required', 'string', 'max:100', app('company')['is_item_name_unique'] ? Rule::unique('items', 'name') : null],
                        'itemType' => ['required',
                                        function ($attribute, $value, $fail) {
                                            if (!in_array(strtoupper($value), ['PRODUCT', 'SERVICE'])) {
                                                $fail(__('item.item_type_should_not_empty'));
                                            }
                                            return true;
                                        },
                                    ],
                        
                    ],[
                        'itemName.required' => __('item.item_name_should_not_empty'),
                        'itemName.string' => 'Item Name should be a string',
                        'itemName.max' => 'Item Name max 255 letters.',
                        'itemName.unique' => __('item.item_name_already_exist'),
                        'itemType.required' => __('item.item_type_should_not_empty'),
                        
                    ]
                );

             

                if ($validator->fails()) {
                    throw new \Exception($validator->errors()->first(). " " . $recordDetails);
                }

                $itemCategoryId = (function() use ($recordDetails, $category) {
                    $response = $this->saveCategory($category);
                    if (!$response['status']) {
                        throw new \Exception($response['message'] . " " . $recordDetails);
                    }
                    return $response['id'];
                })();
                $brandId = (function() use ($recordDetails, $brand) {
                    $response = $this->saveBrand($brand);
                    if (!$response['status']) {
                        throw new \Exception($response['message'] . " " . $recordDetails);
                    }
                    return $response['id'];
                })();

             
                // $itemModel = Item::create([
                //     'count_id'          =>  (Item::select('count_id')->orderBy('id', 'desc')->first()?->count_id ?? 0)+1,
                //     'is_service'        =>  (strtoupper($itemType) == 'PRODUCT') ? 0 : 1,
                //     'item_code'         =>  $itemCode,
                //     'name'              =>  $itemName,
                //     'description'       =>  $description,
                //     'item_category_id'  =>  $itemCategoryId,
                //     'brand_id'          =>  $brandId,
                //     'status'                    =>  1,
                // ]);
                
                $itemModel = Item::create([
                    'count_id'          =>  (Item::select('count_id')->orderBy('id', 'desc')->first()?->count_id ?? 0)+1,
                    'is_service'        =>  (strtoupper($itemType) == 'PRODUCT') ? 0 : 1,
                    'item_code'         =>  $itemCode,
                    'name'              =>  $itemName,

                    'item_category_id'  =>  $itemCategoryId,
                    'brand_id'          =>  $brandId,

                    'conversion_rate'   =>  1,
                    'sale_price'                =>  0,
                    'is_sale_price_with_tax'    =>   0,
                    'sale_price_discount'       =>   0,
                    'sale_price_discount_type'  =>   'fixed',

                    'wholesale_price'            =>   0,
                    'is_wholesale_price_with_tax'=>   0,

                    'purchase_price'            =>   0,
                    'is_purchase_price_with_tax'=>   0,

                    // 'tax_id'                    =>  $taxId,

                    'mrp'                       =>   0,
                    'msp'                       =>   0,

                    'tracking_type'             =>  'general',
                    'min_stock'                 =>   0,
                    'status'                    =>  1,
                ]);
                /**
                 * Record Item Transaction
                 * Import ItemTransactionService
                 * @return Model
                 * */
                $transactionResponse = $this->itemTransactionService->recordItemTransactionEntry($itemModel, [
                    'item_id'                   => $itemModel->id,
                    'transaction_date'          => Carbon::now()->format('Y-m-d'),
                    'warehouse_id'              => $request->warehouse_id,
                    'tracking_type'             => 'regular',
                    'mrp'                       => 0,
                    'unit_id'                   => 1,
                    'quantity'                  => (!empty($opeingStockQty))? $opeingStockQty : 0,
                    'tax_type'                  => 'exclusive',
                    'unit_price'                =>  0,
                    'total'                     => 0,
                ]);

                if(!$transactionResponse){
                    throw new \Exception(__('item.failed_to_record_item_transactions'). " " . $recordDetails);
                }

                //Update Account
                $this->accountTransactionService->itemOpeningStockTransaction($itemModel);


                //collect item id in array
                $itemIds[] = $itemModel->id;

            }//foreach



            $transactionCollection = collect();
            $j = 0;
           

            /**
             * Update Transactions and Item Stock
             * */
           

            DB::commit();

            session(['record' => [
                                    'type' => 'success',
                                    'status' => "Success",
                                    'message' => "Data imported successfully!!",
                                ]]);

            return response()->json([
                'status'    => true,
                'message' => __('app.record_saved_successfully'),
            ]);

        } catch (\Exception $e) {
                DB::rollback();

                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 409);

        }

    }

    public function saveCategory($categoryName) : array
    {
        if(empty($categoryName)){
            $categoryName = $this->defaultItemCategory;
        }
        // Validate category name using Laravel validation rules
        $validator = Validator::make(
            [
            'name' => $categoryName,
            ],
            [
            'name' => 'required|string|max:255', // Adjust table and column names as needed
            ]
        );

        if ($validator->fails()) {
            return [
                'status'    => false,
                'message'   => $validator->errors()->first(),
            ];
        }

        $category = ItemCategory::firstOrCreate(['name' => $categoryName]);

        return [
            'status' => true,
            'message' => 'Category created successfully.',
            'id' => $category->id,
        ];
    }

    public function saveBrand($brandName) : array
    {
        if(empty($brandName)){
            return [
                'status' => true,
                'message' => 'Brand Name is Empty.',
                'id' => null,
            ];
        }
        // Validate category name using Laravel validation rules
        $validator = Validator::make(
            [
            'name' => $brandName,
            ],
            [
            'name' => 'nullable|string|max:255', // Adjust table and column names as needed
            ]
        );

        if ($validator->fails()) {
            return [
                'status'    => false,
                'message'   => $validator->errors()->first(),
            ];
        }

        $brand = Brand::firstOrCreate(['name' => $brandName]);

        return [
            'status' => true,
            'message' => 'Brand created successfully.',
            'id' => $brand->id,
        ];
    }
    public function saveBaseUnit($baseUnitName) : array
    {
        // Validate category name using Laravel validation rules
        $validator = Validator::make(
            [
            'name' => $baseUnitName,
            ],
            [
            'name' => 'required|string|max:255', // Adjust table and column names as needed
            ],
        );

        if ($validator->fails()) {
            return [
                'status'    => false,
                'message'   => $validator->errors()->first(),
            ];
        }

        // Create the category on successful validation
        $baseUnit = Unit::firstOrCreate(['name' => $baseUnitName, 'short_code' => $baseUnitName]);

        return [
                'status'    => true,
                'message'   => '',
                'id'        => $baseUnit->id,
            ];
    }
    public function saveSecondaryUnit($secondaryUnitName) : array
    {
        // Validate category name using Laravel validation rules
        $validator = Validator::make(
            [
            'name' => $secondaryUnitName,
            ],
            [
            'name' => 'nullable|string|max:255', // Adjust table and column names as needed
            ],
        );

        if ($validator->fails()) {
            return [
                'status'    => false,
                'message'   => $validator->errors()->first(),
            ];
        }

        // Create the category on successful validation
        $secondaryUnit = Unit::firstOrCreate(['name' => $secondaryUnitName, 'short_code' => $secondaryUnitName]);

        return [
                'status'    => true,
                'message'   => '',
                'id'        => $secondaryUnit->id,
            ];
    }
    public function savetax(string $taxName, $taxRate) : array
    {
        // Validate category name using Laravel validation rules
        $validator = Validator::make(
            [
            'name' => $taxName,
            ],
            [
            'name' => 'required|string|max:255', // Adjust table and column names as needed
            ]
        );

        if ($validator->fails()) {
            return [
                'status'    => false,
                'message'   => $validator->errors()->first(),
            ];
        }

        // Create the category on successful validation
        $tax = Tax::firstOrCreate(['name' => $taxName, 'rate' => $taxRate]);

        return [
                'status'    => true,
                'message'   => '',
                'id'        => $tax->id,
            ];
    }

    /**
     * Import the Excel Sheet Records
     * @return JsonResponse
     * */
    public function importParties(Request $request)
    {
        $file = $request->file('excel_file');

        $spreadsheet = $this->reader->load($file->getPathname());

        // Select the second sheet
        $sheetNumberOne = 0;
        $sheetOne = $spreadsheet->getSheet($sheetNumberOne); // Sheet indices start at 0, so 1 is the second sheet

        // Get the data from the second sheet
        $data = $sheetOne->toArray();

        $partyTransactionService = new PartyTransactionService();

        try{
            DB::beginTransaction();

            // Do something with the data
            $i = 0;
            if(count($data) <= 1){
                throw new \Exception(__('app.records_not_found'));
            }

            $currencyId = Currency::where('is_company_currency', 1)->first()->id;

            foreach ($data as $row) {
                $i++;
                if($i === 1){
                    continue;
                }

                $partyType              = 'customer';
                $firstName              = trim($row[0]);//required
                $lastName               = trim($row[1]);
                $email                  = trim($row[2]);
                $phone                  = trim($row[3]);
                $mobile                 = trim($row[4]);
                $whatsApp               = trim($row[5]);
                $taxNumber              = trim($row[6]);
                $billingAddress         = trim($row[7]);
                $shippingAddress        = trim($row[8]);
                $isWholesaleCustomer    = trim($row[9]);//Only for Customer (Yes/No)

                $recordDetails = "Sheet:".$sheetNumberOne.", Row:".($i);

                $validator = Validator::make([
                        'partyType'             => $partyType,
                        'firstName'             => $firstName,
                        'lastName'              => $lastName,
                        'email'                 => $email,
                        'phone'                 => $phone,
                        'mobile'                => $mobile,
                        'whatsapp'              => $whatsApp,
                        'taxNumber'             => $taxNumber,
                        'billingAddress'        => $billingAddress,
                        'shippingAddress'       => $shippingAddress,
                    ],[
                        'partyType'     => ['required',
                                            function ($attribute, $value, $fail) {
                                                if (!in_array(strtoupper($value), ['CUSTOMER', 'SUPPLIER'])) {
                                                    $fail('Party type must be either Customer or Supplier.');
                                                }
                                                return true;
                                            },
                                        ],
                        'firstName'     => 'required|string|max:255',
                        'lastName'      => 'nullable|string|max:255',
                        'email'         => ['nullable', 'email', 'max:100', Rule::unique('parties')->where('party_type', $partyType)],
                        'phone'         => ['nullable', 'string', 'max:20', Rule::unique('parties')->where('party_type', $partyType)],
                        'mobile'        => ['nullable', 'string', 'max:20', Rule::unique('parties')->where('party_type', $partyType)],
                        'whatsapp'      => ['nullable', 'string', 'max:20', Rule::unique('parties')->where('party_type', $partyType)],
                        'taxNumber'     => ['nullable', 'string', 'max:100'],
                        'billingAddress'    => ['nullable', 'string', 'max:500'],
                        'shippingAddress'   => ['nullable', 'string', 'max:500'],
                    ],[
                        'firstName.required' => 'First Name should not be a empty',
                        'firstName.string' => 'First Name should be a string',
                        'firstName.max' => 'First Name max 255 letters.',
                        'partyType.required' => "Party type must be either Customer or Supplier",
                    ]
                );

                if ($validator->fails()) {
                    throw new \Exception($validator->errors()->first(). " " . $recordDetails);
                }

              

                /**
                 * If opening balance is not empty then need to select opeing balance type
                 * */
                if(!empty($openingBalance) && $openingBalance>0){
                    if(empty($openingBalanceType)){
                        throw new \Exception('Opening Balance type must be either "To Pay" or "To Receive'. " " . $recordDetails);
                    }
                }

                $partyModel = Party::create([
                    'party_type'            =>  $partyType,
                    'first_name'            =>  $firstName,
                    'last_name'             =>  !empty($lastName)? $lastName: null,
                    'email'                 =>  !empty($email)? $email: null,
                    'phone'                 =>  !empty($phone)? $phone: null,
                    'mobile'                =>  !empty($mobile)? $mobile: null,
                    'whatsapp'              =>  !empty($whatsApp)? $whatsApp: null,
                    'tax_number'            =>  !empty($taxNumber)? $taxNumber: null,
                    'status'                =>  1,
                    'currency_id'           =>  $currencyId,
                    'billing_address'       =>  !empty($billingAddress)? $billingAddress: null,
                    'shipping_address'      =>  !empty($shippingAddress)? $shippingAddress: null,
                    'is_wholesale_customer' =>  ($partyType == 'customer' && !empty($isWholesaleCustomer) && strtoupper($isWholesaleCustomer) == strtoupper('Yes') )? 1 : 0,
                ]);

               

                //Account Create or Update
                $acccountCreateOrUpdate = $this->accountTransactionService->createOrUpdateAccountOfParty(partyId: $partyModel->id, partyName: $partyModel->first_name." ".$partyModel->last_name, partyType: $partyModel->party_type );
                if(!$acccountCreateOrUpdate){
                    throw new \Exception(__('account.failed_to_create_or_update_account'));
                }

            }//foreach

            DB::commit();

            session(['record' => [
                                    'type' => 'success',
                                    'status' => "Success",
                                    'message' => "Data imported successfully!!",
                                ]]);

            return response()->json([
                'status'    => true,
                'message' => __('app.record_saved_successfully'),
            ]);

        } catch (\Exception $e) {
                DB::rollback();

                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 409);

        }

    }

    public function saveState($stateName) : array
    {
        // Validate state name using Laravel validation rules
        $validator = Validator::make(
            [
            'name' => $stateName,
            ],
            [
            'name' => 'required|string|max:255', // Adjust table and column names as needed
            ]
        );

        if ($validator->fails()) {
            return [
                'status'    => false,
                'message'   => $validator->errors()->first(),
            ];
        }

        $state = State::firstOrCreate(['name' => $stateName]);

        return [
            'status' => true,
            'message' => 'Category created successfully.',
            'id' => $state->id,
        ];
    }
}
