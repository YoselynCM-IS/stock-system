<?php

namespace App\Imports\libros;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CodesImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
    }
}
