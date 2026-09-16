<?php

namespace App\Http\Controllers\Items;

use App\Http\Controllers\Controller;
use App\Models\Items\ProductionItemMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use ZipArchive;

class ProductionExportController extends Controller
{
    public const STATUSES = ['Assigning Pending', 'Pending', 'Partial', 'Progress', 'In Progress', 'Packing Pending', 'Completed', 'Cancelled'];

    public function download(Request $request)
    {
        $data = $request->validate([
            'statuses' => ['required', 'array', 'min:1'],
            'statuses.*' => ['required', 'string', 'distinct', Rule::in(self::STATUSES)],
        ]);

        $produced = DB::table('production_list')->whereNull('deleted_at')->select('production_item_master_id')
            ->selectRaw('SUM(quantity) as total')->groupBy('production_item_master_id');
        $packed = DB::table('packing_list')->whereNull('deleted_at')->select('production_item_master_id')
            ->selectRaw('SUM(quantity) as total')->groupBy('production_item_master_id');

        $query = ProductionItemMaster::query()
            ->whereIn('production_item_masters.status', $data['statuses'])
            ->leftJoin('items', 'items.id', '=', 'production_item_masters.item_id')
            ->leftJoin('brands', 'brands.id', '=', 'items.brand_id')
            ->leftJoin('item_categories', 'item_categories.id', '=', 'items.item_category_id')
            ->leftJoin('purchase_order_masters as po', 'po.id', '=', 'production_item_masters.purchase_order_id')
            ->leftJoin('parties', 'parties.id', '=', 'po.customer_id')
            ->leftJoinSub($produced, 'produced', 'produced.production_item_master_id', '=', 'production_item_masters.id')
            ->leftJoinSub($packed, 'packed', 'packed.production_item_master_id', '=', 'production_item_masters.id')
            ->select(['production_item_masters.id', 'production_item_masters.production_type', 'production_item_masters.status',
                'production_item_masters.requested_qty', 'items.name as product_name', 'brands.name as brand_name',
                'item_categories.name as category_name', 'po.purchase_order_id as work_order', 'po.due_date', 'po.po_date',
                'parties.first_name', 'parties.last_name', 'produced.total as produced_qty', 'packed.total as packed_qty']);

        $sheet = tempnam(sys_get_temp_dir(), 'production_sheet_');
        $xlsx = tempnam(sys_get_temp_dir(), 'production_export_');
        if ($sheet === false || $xlsx === false) abort(500, 'Unable to prepare export.');
        try {
            $handle = fopen($sheet, 'wb');
            if (!$handle) abort(500, 'Unable to prepare export.');
            fwrite($handle, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols><col min="1" max="1" width="17" customWidth="1"/><col min="2" max="2" width="28" customWidth="1"/><col min="3" max="3" width="25" customWidth="1"/><col min="4" max="4" width="38" customWidth="1"/><col min="5" max="6" width="24" customWidth="1"/><col min="7" max="9" width="23" customWidth="1"/><col min="10" max="11" width="20" customWidth="1"/><col min="12" max="12" width="23" customWidth="1"/></cols><sheetData>');
            $rowNumber = 0;
            $writeRow = function (array $values, int $style = 0, ?float $height = null) use ($handle, &$rowNumber): void {
                ++$rowNumber;
                fwrite($handle, '<row r="'.$rowNumber.'"'.($height ? ' ht="'.$height.'" customHeight="1"' : '').'>');
                foreach ($values as $index => $value) {
                    $letter = chr(65 + $index);
                    $safe = htmlspecialchars((string) ($value ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
                    fwrite($handle, '<c r="'.$letter.$rowNumber.'" s="'.$style.'" t="inlineStr"><is><t>'.$safe.'</t></is></c>');
                }
                fwrite($handle, '</row>');
            };
            $writeRow(['Production ID', 'Customer', 'Work Order', 'Product', 'Brand', 'Category', 'Requested Qty', 'Production Remaining', 'Packing Remaining', 'Due Date', 'Ageing (Days)', 'Status'], 3, 34);
            $query->orderByDesc('production_item_masters.id')->chunk(500, function ($rows) use ($writeRow) {
                foreach ($rows as $row) {
                    $writeRow([$row->id, trim(($row->first_name ?? '').' '.($row->last_name ?? '')) ?: $row->production_type,
                        $row->work_order, $row->product_name, $row->brand_name, $row->category_name, $row->requested_qty,
                        (float) $row->requested_qty - (float) $row->produced_qty,
                        (float) $row->requested_qty - (float) $row->packed_qty,
                        $row->due_date ? date('d-m-Y', strtotime($row->due_date)) : '',
                        $row->po_date ? now()->diffInDays($row->po_date).' days' : '', $row->status], 4);
                }
            });
            fwrite($handle, '</sheetData><autoFilter ref="A1:L'.$rowNumber.'"/></worksheet>');
            fclose($handle);

            $zip = new ZipArchive();
            if ($zip->open($xlsx, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) abort(500, 'Unable to prepare export.');
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><bookViews><workbookView/></bookViews><sheets><sheet name="Productions" sheetId="1" r:id="rId1"/></sheets></workbook>');
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
            $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="3"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="16"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts><fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF17365D"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FF28659B"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFDCE3EB"/></left><right style="thin"><color rgb="FFDCE3EB"/></right><top style="thin"><color rgb="FFDCE3EB"/></top><bottom style="thin"><color rgb="FFDCE3EB"/></bottom><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="5"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment vertical="center"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center"/></xf><xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf></cellXfs></styleSheet>');
            $zip->addFile($sheet, 'xl/worksheets/sheet1.xml');
            $zip->close();
            unlink($sheet);

            return response()->download($xlsx, 'production-export-'.now()->format('Ymd-His').'.xlsx',
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);
        } catch (\Throwable $exception) {
            if (is_resource($handle ?? null)) fclose($handle);
            if (file_exists($sheet)) unlink($sheet);
            if (file_exists($xlsx)) unlink($xlsx);
            throw $exception;
        }
    }
}
