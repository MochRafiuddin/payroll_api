@extends('app')
@section('content-app')
<style type="text/css">
		.custom-margin-top{
			margin-top: 6rem !important;
		}
	</style>
    <!-- <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth">
                <div class="row w-100">
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-light text-left p-5">
                            <div class="brand-logo">
                                <img src="{{asset('/')}}assets/images/logo.svg" alt="logo">
                            </div>
                            <h4>Hello! let's get started</h4>
                            <h6 class="font-weight-light">Sign in to continue.</h6>
                            <form class="pt-3" action="{{url('auth')}}" method="post">
                                @csrf
                                <div class="form-group">
                                    <input type="text" name="username" class="form-control form-control-lg" placeholder="Username">
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Password">
                                </div>
                                <div class="mt-3">
                                    <input type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn"
                                        value="SIGN IN"/>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            content-wrapper ends
        </div>
        page-body-wrapper ends
    </div> -->
    <!-- container-scroller -->
    <!-- plugins:js -->
    <div class="container-scroller">
		    <div class="container-fluid page-body-wrapper full-page-wrapper">
		      <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
		        <div class="row flex-grow">
		          <div class="col-lg-6 d-flex align-items-center justify-content-center">
		            <div class="auth-form-transparent text-left p-3 h-100 custom-margin-top">
		              <div class="brand-logo">
		                <!-- <img src="../../../../images/logo.svg" alt="logo"> -->
		              </div>
		              	<div class="row mb-4">
		              		<div class="col text-center">
		              			<img src="{{asset('/')}}assets/images/lotte.png" width="100%">
		              		</div>
		              	</div>			    		
						  	@if(session('success'))
                                <p class="alert alert-danger text-center">{{ session('success') }}</p>
                        	@endif
		              <!-- <form class="pt-4 mt-4" action="" method="post"> -->
                        <form class="pt-4 mt-4" action="{{url('auth')}}" method="post">
                        @csrf
		                <div class="form-group">
		                  <label for="exampleInputEmail">Username</label>
		                  <div class="input-group">
		                    <div class="input-group-prepend bg-transparent">
		                      <span class="input-group-text bg-transparent border-right-0">
		                        <i class="mdi mdi-account-outline text-primary"></i>
		                      </span>
		                    </div>
		                    <input type="text" name="username" class="form-control form-control-lg" placeholder="Username">	                    
		                  </div>
		                </div>
		                <div class="form-group">
		                  <label for="exampleInputPassword">Password</label>
		                  <div class="input-group">
		                    <div class="input-group-prepend bg-transparent">
		                      <span class="input-group-text bg-transparent border-right-0">
		                        <i class="mdi mdi-lock-outline text-primary"></i>
		                      </span>
		                    </div>
		                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Password">
		                  </div>
		                </div>		              
		                <!-- <div class="my-2 d-flex justify-content-between align-items-center">
		                  <div class="form-check">
		                    <label class="form-check-label text-muted">
		                      <input type="checkbox" class="form-check-input">
		                      Keep me signed in
		                    </label>
		                  </div>
		                  <a href="#" class="auth-link text-black">Forgot password?</a>
		                </div> -->
		                <div class="my-3">
		                  <button class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn" type="submit">LOGIN</button>
		                </div>
		                <!-- <div class="mb-2 d-flex">
		                  <button type="button" class="btn btn-facebook auth-form-btn flex-grow mr-1">
		                    <i class="mdi mdi-facebook mr-2"></i>Facebook
		                  </button>
		                  <button type="button" class="btn btn-google auth-form-btn flex-grow ml-1">
		                    <i class="mdi mdi-google mr-2"></i>Google
		                  </button>
		                </div>
		                <div class="text-center mt-4 font-weight-light">
		                  Don't have an account? <a href="register-2.html" class="text-primary">Create</a>
		                </div> -->
		              </form>
		              <div class="row" style="position: absolute; bottom: 15px; right: 0; left: 0;">	
	            		<div class="col text-center">
	            				<p class=" m-auto">	Powered By <a class="text-info" href="https://www.aptikma.co.id/" target="_blank">	Aptikma Tekhnologi Indonesia</a></p>	
	            		</div>
		            </div>
		            </div>

		          </div>
		          <div class="col-lg-6 login-half-bg d-flex flex-row">
		          	<img class="img w-100" src="{{asset('/')}}assets/images/office-building.jpg">
		           
		          </div>
		        </div>
		      </div>
		      <!-- content-wrapper ends -->
		    </div>
		    <!-- page-body-wrapper ends -->
		  </div>
@endsection