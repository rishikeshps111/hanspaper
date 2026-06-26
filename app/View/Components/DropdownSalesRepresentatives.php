<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\SalesRepresentatives\SalesRepresentative;

class DropdownSalesRepresentatives extends Component
{
    /**
     * Sales representatives collection
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    public $representatives;

    /**
     * Selected option value
     *
     * @var mixed
     */
    public $selected;

    /**
     * Dropdown name attribute
     *
     * @var string
     */
    public $dropdownName;
    public $required;

    /**
     * Create a new component instance.
     */
    public function __construct(string $dropdownName, $selected = null, $required = true)
    {
        $this->representatives = SalesRepresentative::where('status', 'Active')
            ->orderBy('full_name')
            ->get();

        $this->selected = $selected;
        $this->dropdownName = $dropdownName;
        $this->required = $required;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dropdown-salerepresentatives');
    }
}