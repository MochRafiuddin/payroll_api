@extends('template')
@section('content')
<?php 
use App\Traits\Helper;  
$name[] = 'id_action';
?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">{{$titlePage}} Role</h6>
                <form action="{{$url}}" method="post">
                    @csrf
                    <div class="row">
                        @foreach($menu as $key)
                        <div class="form-group col-4">
                            <label for="exampleInputEmail1">{{$key->nama_menu}}</label>
                            <?php
                                $action = App\Models\MAction::withMenu($key->id_menu)->get();
                            ?>
                            @foreach($action as $ac)
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" name="id_action[]"
                                            id="optionscheckbox" value="{{$ac->id_action}}" {{Helper::showDataChecked2($data,$name[0],$ac->id_action)}}>
                                        {{$ac->nama_role}}
                                        <i class="input-helper"></i></label>
                                </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                    <input type="submit" class="btn btn-success" value="Simpan" />
                </form>
            </div>
        </div>
    </div>
</div>

@endsection