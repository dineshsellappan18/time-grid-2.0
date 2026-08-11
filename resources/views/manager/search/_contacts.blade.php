@if(count($items))
<div class="card">
    <ul class="list-group">
        @each('manager.search._contact', $items, 'contact')
    </ul>
</div>
@endif