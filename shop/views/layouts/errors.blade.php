@if (count($errors) > 0)
    <div class="Huialert Huialert-error">
        <ul>
            @foreach ($errors->all() as $error)
                <li><i class="Hui-iconfont">&#xe6a6;</i> {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif