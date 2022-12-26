<?php 
use App\Traits\Helper;
?>
<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo w-100"><img src="{{asset('/')}}assets/images/brand-logo-example.png" alt="logo" /></a>
        <!-- <a class="navbar-brand brand-logo-mini" href="index.html"><img src="{{asset('/')}}assets/images/logo-mini.svg" alt="logo" /></a> -->
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
        </button>
        <ul class="navbar-nav navbar-nav-right">
            <li style="margin-right:10px">
                <a href="{{url('penggajian/periode')}}" class="btn btn-outline-success">
                        {{Helper::convertBulan(Session::get('periode_bulan'))}} {{Session::get('periode_tahun')}}
                </a>
            </li>
            <li> 
                @if(Session::get('locale')=="en")
                <a style="color:black;font-weight: bold;">EN</a> <span style="color:black;font-weight: bold;">|</span> <a href="{{url('setlocale/id')}}" id="indo" style="color:gray">ID</a>                                
                @else                
                <a href="{{url('setlocale/en')}}" style="color:gray">EN</a> <span style="color:black;font-weight: bold;">|</span> <a style="color:black;font-weight: bold;">ID</a>                
                @endif               
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link count-indicator dropdown-toggle notifbaru" id="notificationDropdown" href="javascript:void(0)" data-toggle="dropdown" data-user="{{ Auth::user()->id_user }}">
                    <i class="mdi mdi-bell-outline mx-0"></i>
                    @if(Auth::user()->new_notif > 0)
                        <span class="count" id="baru"></span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown" style="width: 500px !important">
                    <a class="dropdown-item">
                        <p class="mb-0 font-weight-normal col-9">New notifications
                        </p>
                        <span class="badge badge-pill badge-warning">View all</span>
                    </a>                    
                    <?php
                        $notif = DB::table('notif')
                        ->where('deleted',1)
                        ->where('id_user',Auth::user()->id_user)
                        ->orderBy('id_notif', 'desc')
                        ->limit(5)
                        ->get();
                        ?>
                    @foreach($notif as $not)
                        <div class="dropdown-divider"></div>
                        @if($not->is_read==0)
                            <a class="dropdown-item preview-item" href="javascript:void(0)" style="background-color: #ebebe0;"
                             onclick="clickUrl(this,{{ $not->id_notif }},'{{ $not->url }}')">
                        @else
                            <a class="dropdown-item preview-item" href="javascript:void(0)" onclick="CkUrl('{{ $not->url }}')">
                        @endif
                            <div class="preview-item-content" style="white-space: initial;">
                                <h6 class="preview-subject font-weight-medium">{{$not->judul}}</h6>
                                <p class="font-weight-light small-text mb-0">
                                    {{$not->isi}}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </li>
            <!-- <li class="nav-item dropdown">
                <a class="nav-link count-indicator dropdown-toggle" id="messageDropdown" href="#" data-toggle="dropdown"
                    aria-expanded="false">
                    <i class="mdi mdi-email-outline mx-0"></i>
                    <span class="count"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list"
                    aria-labelledby="messageDropdown">
                    <div class="dropdown-item">
                        <p class="mb-0 font-weight-normal float-left">You have 7 unread mails
                        </p>
                        <span class="badge badge-info badge-pill float-right">View all</span>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item preview-item">
                        <div class="preview-thumbnail">
                            <img src="https://via.placeholder.com/36x36" alt="image" class="profile-pic">
                        </div>
                        <div class="preview-item-content flex-grow">
                            <h6 class="preview-subject ellipsis font-weight-medium">David Grey
                                <span class="float-right font-weight-light small-text">1 Minutes ago</span>
                            </h6>
                            <p class="font-weight-light small-text mb-0">
                                The meeting is cancelled
                            </p>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item preview-item">
                        <div class="preview-thumbnail">
                            <img src="https://via.placeholder.com/36x36" alt="image" class="profile-pic">
                        </div>
                        <div class="preview-item-content flex-grow">
                            <h6 class="preview-subject ellipsis font-weight-medium">Tim Cook
                                <span class="float-right font-weight-light small-text">15 Minutes ago</span>
                            </h6>
                            <p class="font-weight-light small-text mb-0">
                                New product launch
                            </p>
                        </div>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item preview-item">
                        <div class="preview-thumbnail">
                            <img src="https://via.placeholder.com/36x36" alt="image" class="profile-pic">
                        </div>
                        <div class="preview-item-content flex-grow">
                            <h6 class="preview-subject ellipsis font-weight-medium"> Johnson
                                <span class="float-right font-weight-light small-text">18 Minutes ago</span>
                            </h6>
                            <p class="font-weight-light small-text mb-0">
                                Upcoming board meeting
                            </p>
                        </div>
                    </a>
                </div>
            </li> -->
            <li class="nav-item nav-profile dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                    <img src="https://via.placeholder.com/30x30" alt="profile" />
                    @php
                        $nama=explode(" ",Auth::user()->name);
                    @endphp
                    <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{$nama[0]}}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                    <h6 class="dropdown-header text-center">{{Auth::user()->name}}</h6>
                    <div class="dropdown-divider"></div>
                    @if(Session::get('can_switch_account')==1 && Session::get('id_user')!=Auth::user()->id_user)
                    <a class="dropdown-item" href="{{url('user/switch-user/'.session::get('id_user'))}}">
                        <i class="mdi mdi-logout text-primary"></i>
                        Back to {{Session::get('name')}}
                    </a>
                    @endif
                    <a class="dropdown-item" href="{{url('logout')}}">
                        <i class="mdi mdi-logout text-primary"></i>
                        Logout
                    </a> 
                </div>
            </li>
            <!-- <li class="nav-item nav-settings d-none d-lg-block">
                <a class="nav-link" href="#">
                    <i class="mdi mdi-apps"></i>
                </a>
            </li> -->
        </ul>

    </div>
</nav>
@push('js')
<script>    
    
    $('body').on('click', '.notifbaru', function () {
        var user = $(this).data('user');
        $.ajax({          
          url: "{{ url('update-user-new-notif') }}/"+user,
          type: "GET",
          dataType: 'json',
          success: function (data) {
            $("#baru").remove();         
          },
          error: function (data) {
              console.log('Error:', data);
          }
      });
    });

    function clickUrl(selectObject,i,u) {        
        var url='{{url("")}}/'+u;
        jQuery.ajax({
            type: 'GET',
            url: '{{ url("update-notif") }}/'+i,
            success: function(result) {
                selectObject.removeAttribute("style");
                window.open(url,"_self");
            }
        });
    }
    
    function CkUrl(u) {        
        var url='{{url("")}}/'+u;        
        window.open(url,"_self");            
    }

</script>
@endpush