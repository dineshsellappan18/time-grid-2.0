{!! Form::open([
    'method' => 'post',
    'url' => route('manager.search', $business),
    'class' => 'tg-sidebar-search',
    'role' => 'search',
]) !!}
<div class="input-group">
    <input type="text" name="criteria" id="search" class="form-control" placeholder="{{ trans('app.search.placeholder') }}" aria-label="{{ trans('app.search.placeholder') }}">
    <button type="submit" name="search" id="search-btn" class="btn btn-outline-secondary">
        <i class="fa fa-search"></i>
    </button>
</div>
{!! Form::close() !!}
