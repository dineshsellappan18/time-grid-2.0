<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <div class="float-end d-none d-sm-block">
        @if (app()->environment('local'))
        <span class="float-end">{!! Label::danger('LOCAL') !!}
            <span class="text-danger">&nbsp;{{ trans('app.footer.local') }}</span>
        </span>
        @endif
        @if (app()->environment('demo'))
        <span class="float-end">{!! Label::danger('DEMO') !!}
            <span class="text-danger">&nbsp;{{ trans('app.footer.demo') }}</span>
        </span>
        @endif
    </div>
    <!-- Default to the left -->
    <strong>powered by <a href="https://timegrid.io">timegrid</a></strong>
</footer>