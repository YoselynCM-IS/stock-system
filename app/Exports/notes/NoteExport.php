<?php

namespace App\Exports\notes;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Note;

class NoteExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function view(): View
    {
        $note = Note::whereId($this->id)->with('registers.libro')->first();
        return view('download.excel.notes.note', [
            'note' => $note
        ]);
    }
}
