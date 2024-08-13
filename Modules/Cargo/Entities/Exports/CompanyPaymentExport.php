<?php

namespace Modules\Cargo\Entities\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CompanyPaymentExport implements FromArray, WithHeadings
{

    protected $data, $head;

    public function __construct(array $data, array $head)
    {
        $this->data = $data;
        $this->head = $head;
    }
    
    public function headings(): array
    {
        return $this->head;
    }

    public function array(): array
    {
        return $this->data;
    }

    
}
