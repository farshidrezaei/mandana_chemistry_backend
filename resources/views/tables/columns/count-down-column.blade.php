@if(is_null($formatState($getState())))
    <div style="display:flex;justify-content: center;width: 100%" dir="ltr" class="timer" >
        <span>-</span>
    </div>

@elseif((int)$formatState($getState())<=0)
    <div style="display:flex;justify-content: center;width: 100%" dir="ltr" class="timer" >
        <span>00</span>:
        <span>00</span>:
        <span>00</span>
    </div>
@else
    <div style="display:flex;justify-content: center;width: 100%" dir="ltr" class="timer" x-data="timer(((new Date).getTime() + parseInt({{(int)$formatState($getState())}}) * 1000))" x-init="init();">
        <span x-text="time().hours"></span>:
        <span x-text="time().minutes"></span>:
        <span x-text="time().seconds"></span>
    </div>

@endif



