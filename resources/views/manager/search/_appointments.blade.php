@if(count($items))
<div class="card">
    <ul class="list-group">
        @each('manager.search._appointment', $items, 'appointment')
    </ul>
</div>
@endif