<div class="btn-group" data-toggle="buttons">
    @foreach($options as $option => $label)
    <label class="btn btn-default btn-sm {{ \Request::get('type', 'inbox') == $option ? 'active' : '' }}">
        <input type="radio" class="message-type" value="{{ $option }}">{{$label}}
    </label>
    @endforeach
</div>