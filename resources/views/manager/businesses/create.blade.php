@extends('layouts.bare')

@section('title', trans('manager.businesses.create.title'))
@section('subtitle', trans('manager.businesses.msg.register', ['plan' => trans($plan)]))

@section('content')
<div class="col-sm-12 col-sm-offset-0 col-md-8 col-md-offset-2">

    <div class="card">

        <div class="card-header">
        {{ trans('manager.businesses.create.title') }}
        </div>

        <div class="card-body">
            {!! Form::model($business, ['route' => ['manager.business.store'], 'id' => 'registration', 'data-bs-toggle' => 'validator', 'class' => 'form-horizontal']) !!}
            <fieldset>
            {!! Form::hidden('plan', $plan) !!}
            {!! Form::hidden('country_code', $countryCode) !!}
            {!! Form::hidden('locale', $locale) !!}
            @include('manager.businesses._form', ['submitLabel' => trans('manager.businesses.btn.store')])
            </fieldset>
            {!! Form::close() !!}
        </div>

    </div>

</div>
@endsection

@push('footer_scripts')
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {

    var count = 0;
    $('button[type=submit]').click(function(){
        count++;
        if(count == 5) {
            var script = document.createElement( 'script' );
            script.type = 'text/javascript';
            script.src = '{{ TidioChat::src() }}';
            document.querySelectorAll('body').append( script );
            alert('{!! trans('auth.register.need_help') !!}');
        }
    });
});
</script>
@endpush