<?php

namespace App\Http\Livewire;

use Illuminate\View\View;
use Livewire\Component;

class CountDown extends Component
{
    public ?int $seconds = null;
    public string $result = "";


    public function mount(?int $seconds): void
    {
        $this->seconds = $seconds;
        if ($this->seconds === null || $this->seconds === 0) {
            $this->result = "-";
        } else {
            $this->result = today()->addSeconds($this->seconds)->format('H:i:s');
        }
    }

    public function run()
    {
        if ($this->seconds === null || $this->seconds === 0) {
            $this->result = "-";
        } else {
            if ($this->seconds > 0) {
                $this->seconds--;
                $this->result = today()->addSeconds($this->seconds)->format('H:i:s');
            }
        }
    }

    //    public function render(): View
    //    {
    //        return view('livewire.count-down');
    //    }
    public function render(): View
    {
        return view('livewire.count-down');
    }
}
