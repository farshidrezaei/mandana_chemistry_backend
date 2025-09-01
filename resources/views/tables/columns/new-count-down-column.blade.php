@if($formatState($getState()) !== null && $formatState($getState()) !== 0)
    @livewire('count-down', ['seconds' => (int) $formatState($getState())], key('countdown-'.$getRecord()->id))
@endif


