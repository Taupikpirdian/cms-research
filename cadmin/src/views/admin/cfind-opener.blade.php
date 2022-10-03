<link href='//fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>

<link rel='stylesheet' type='text/css' href="{{ asset('cfind/assets/css/style.css') }}">
<link rel='stylesheet' type='text/css' href="{{ asset('cfind/assets/css/font-awesome.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('cfind/imgAreaSelection/css/imgareaselect-default.css') }}" />
<link rel='stylesheet' type='text/css' href="{{ asset('admin-wcms/css/style-cfind.css') }}">

<div class='cfind-main'>
	<div class='cfind-tools'>
		<ul>
			<li class='cfind-create-folder' data-disable="{{ !empty($conf['is_search']) || $conf['disable']['add'] }}"><span class='fa fa-folder'><span class="fa fa-plus"></span></span>
				<div>
					<label>Create new folder</label>
					<form method='post'>
                        <input type="hidden" name="_token" value="{!! csrf_token(); !!}">
						<input type='text' name='folder' value='New Folder'><input type='submit' value='Create'>
					</form>
				</div>
			</li>
			<li class='cfind-create-file' data-disable="{{ !empty($conf['is_search']) || $conf['disable']['add'] }}"><span class='fa fa-file-photo-o'></span><span class="fa fa-plus"></span>
				<div>
					<label>Upload new file</label>
					<form method='post' enctype='multipart/form-data'>
                        <input type="hidden" name="_token" value="{!! csrf_token(); !!}">
						@for($i=1; $i<=5; $i++)
							<input type='file' name='name_{{$i}}'>
						@endfor
						<label><input type='checkbox' name='overide' value='1'><span>Overwrite if exists</span></label><br/>
						<input type='submit' value='Upload'><br/>
					</form>
				</div>
			</li>
			<li class='cfind-delete-folder' data-disable="{{ !empty($conf['is_search']) || $conf['disable']['delete'] }}"><span class='fa fa-remove'></span>
				<div>
					<form method="post">
                        <input type="hidden" name="_token" value="{!! csrf_token(); !!}">
						<input type='hidden' name='path'>
						<input type="submit" name="delete" value="Delete Folder">
					</form>
				</div>	
			</li>
			<li data-disable="{{ !empty($conf['is_search']) || !$conf['url_back'] }}"><a href="{{ $conf['url_back'] }}" class='fa fa-arrow-up'></a></li>
			<li class='cfind-path'>
				 {!! $conf['path'] !!}
			</li>
			<li class='cfind-search'>
				<form method='post'>
                    <input type="hidden" name="_token" value="{!! csrf_token(); !!}">
					<input type='text' name='search' value="{{ $conf['is_search'] }}" placeholder='Search'>
				</form>
			</li>
		</ul>
	</div>
	
	<div class='cfind-explorer'>
		<ul>
			{!! $conf['dir'] !!}
		</ul>
	</div>
	
	<div class='cfind-items'>
		{!! $conf['msg'] !!}
		
		<ul>
            {!! $conf['file'] !!}
		</ul>
	</div>
	
	<div class='cfind-detail'>
		<div style="display:none;">
			<h3></h3>
			<div class='cfind-item-file'>
				<figure></figure>
			</div>
			<p>
			
			</p>
			
            @if (!$conf['disable']['delete']) 
                <a href=# class='remove' ><span class='fa fa-remove'></span> Remove File</a>
            @endif
            
            @if (Request::query('opener') == 'ckeditor') 
                <a href=# class="use cfind-use-ckeditor"><span class="fa fa-map-pin"></span> Use</a>
            @endif
            
            @if (Request::query('opener') == 'cfind')
                @if (array_get($conf, 'thumb.width') || array_get($conf, 'thumb.height'))
                    <br><br>
                    <div class="cfind-message">
                        Image need to be resized to :<br/>

                        @if (array_get($conf, 'thumb.width'))
                            Width : {{ array_get($conf, 'thumb.width') }}px<br/>
                        @endif

                        @if (array_get($conf, 'thumb.height'))
                            Height : {{ array_get($conf, 'thumb.height') }}px</br/>
                        @endif
                    </div>
                @endif
                
                @if (array_get($conf, 'thumb.type') == true) 
                    @if (array_get($conf, 'thumb.is-crop'))
                        <a href=# class="use cfind-use-crop"
                           crop-ratio="{{ array_get($conf, 'thumb.crop-ratio') }}"
                           crop-width="{{ array_get($conf, 'thumb.width') }}"
                           crop-height="{{ array_get($conf, 'thumb.height') }}"
                           style="width:150px;"
                           ><span class="fa fa-crop"></span> Select crop area</a>
                    @endif
                    <a href=# class="use cfind-use-auto-crop"
                       crop-width="{{ array_get($conf, 'thumb.width') }}"
                       crop-height="{{ array_get($conf, 'thumb.height') }}"
                       ><span class="fa fa-image"></span> Auto resize</a>
                    <form method="post" id="auto_crop">
                        <input type="hidden" name="_token" value="{!! csrf_token(); !!}">
                        <input type="hidden" name="do" value="resize">
                        <input type="hidden" name="thumb" value="{{ request()->get('thumb') }}">
                        <input type="hidden" name="source">
                    </form>
                @else
					<form method="post" id="use" style="display:inline-block;">
                        <input type="hidden" name="_token" value="{!! csrf_token(); !!}">
                        <input type="hidden" name="do" value="use">
                        <input type="hidden" name="source">
						<a href=# class="use" onClick="$('#use').trigger('submit');return false;"><span class="fa fa-map-pin"></span> Use</a>
                    </form>
                @endif
            @endif
            
			<div class="cfind-div cfind-delete-file-container" style="display:none;">
				<form method="post">
					<input type="hidden" name="_token" value="{!! csrf_token(); !!}">
                    <input type='hidden' name='path'>
					<input type="submit" name="delete" value="Delete File">
				</form>
			</div>
			
		</div>
	</div>
	
	<div style='clear:both;'></div>
</div>

@if (array_get($conf, 'thumb.is-crop') == true)
<div class='cfind-select-area'>
    
    <form method="post">
    	<div class="box-crop-side">
        	<div class="left">
		    	<h1>Select crop area</h1>
		    	<div class='selection-area'></div>
	            <input type="hidden" name="_token" value="{!! csrf_token(); !!}">
	            <input type="hidden" name="do" value="resize">
	            
            </div>
            <div class="right">
            	Selected area :<br/>
	            <div class="tx"><input type="text" name="width" class="size" readonly><span class="sat">px</span></div>
	            <span class="xsat">x</span> 
	            <div class="tx"><input type="text" name="height" class="size" readonly><span class="sat">px</span></div><br/><br/>
	            Recommended size :<br/>
	            <div class="tx"><input type="text" name='toWidth' class="size" readonly value="{{ array_get($conf, 'thumb.width') }}"><span class="sat">px</span></div>
	            <span class="xsat">x</span> 
	            <div class="tx"><input type="text" name='toHeight' class="size" readonly value="{{ array_get($conf, 'thumb.height') }}"><span class="sat">px</span></div>
	            <br/><br/>
	            
	            <a href=# class="btn selection-area-back"><span class="fa fa-chevron-left"></span> Cancel</a>
	            <a href=# class="btn selection-area-confirm" style="display:none;"><span class="fa fa-crop"></span> Confirm</a>
	            <input type="hidden" name="x1"><input type="hidden" name="y1">
	            <input type="hidden" name="thumb" value="{{ request()->get('thumb') }}">
	            <input type="hidden" name="source">
            </div>
    	</div>
    </form>
</div>
@endif

<script src="{{ asset('cfind/assets/js/jquery.js') }}"></script>
<script src="{{ asset('cfind/assets/js/cfind.js') }}"></script>
<script type="text/javascript" src="{{ asset('cfind/imgAreaSelection/scripts/jquery.imgareaselect.pack.js') }}"></script>