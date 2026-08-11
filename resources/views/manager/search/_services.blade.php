@if(count($items))
<div class="card">
    <ul class="list-group">
        @each('manager.search._service', $items, 'service')
    </ul>
</div>
@endif