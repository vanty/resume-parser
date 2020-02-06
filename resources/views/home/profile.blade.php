<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <link rel="stylesheet" href="/css/custom.css" rel="stylesheet">

    <title>CV Parsing Tools</title>
</head>
<body>

<header class="bg-white">
    <nav class="navbar navbar-expand-md navbar-light container">
        <div class="d-flex order-0">
            <a class="navbar-brand align-items-center d-flex mr-1 font-weight-bold" href="#">
                <span>
                    Bestinskill<sup class="text-warning">.com</sup>
                </span>
            </a>
        </div>
        <button class="navbar-toggler border-0" type="button" data-toggle="collapse" data-target="#collapsingNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <form class="ml-md-5 pl-md-5 mt-3 mt-md-0 navbar-search" method="post" enctype="multipart/form-data" action="{{ route('file.upload') }}">
            <input type="hidden" name="_token" value="{!! csrf_token() !!}">
            <div class="custom-file">
                <input type="file" name="file" class="custom-file-input" id="file">
                <label class="custom-file-label" for="customFile">Choose file</label>
            </div>
            <button type="submit" class="btn btn-primary upload-btn">Upload</button>
        </form>
        <div class="navbar-collapse justify-content-end collapse" id="collapsingNavbar">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link px-2" href="">Home</a>
                </li>
                {{--<li class="nav-item active">--}}
                    {{--<a class="nav-link px-2" href="">Profile</a>--}}
                {{--</li>--}}
                {{--<li class="nav-item">--}}
                    {{--<a class="nav-link px-2" href="">Friends</a>--}}
                {{--</li>--}}
                {{--<li class="nav-item">--}}
                    {{--<a class="nav-link px-2" href="">Messages</a>--}}
                {{--</li>--}}
            </ul>
            <div class="btn-group ml-4">
                <a href="javascript:;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                    <img class="navbar-avatar rounded-circle" src="/img/avatar.jpg" />
                </a>
                <div class="dropdown-menu dropdown-menu-right mt-3">
                    <a class="dropdown-item" href="">Action</a>
                    <a class="dropdown-item" href="">Another action</a>
                    <a class="dropdown-item" href="">Something else here</a>
                </div>
            </div>
        </div>
    </nav>
</header>

@if (session('user'))
@php $user = session('user'); @endphp
<div class="container py-4 my-2">
    <div class="row">
        <div class="col-md-4 pr-md-5">
            <img class="w-100 rounded border" src="{!! ($user->image)? asset('storage/images/'.$user->image) : '/img/avatar.jpg' !!}" />
            {{--<div class="pt-4 mt-2">--}}
                {{--<section class="mb-4 pb-1">--}}
                    {{--<h3 class="h6 font-weight-light text-secondary text-uppercase">Work Experiences</h3>--}}
                    {{--<div class="work-experience pt-2">--}}
                        {{--<div class="work mb-4">--}}
                            {{--<strong class="h5 d-block text-secondary font-weight-bold mb-1">Prodesign Inc</strong>--}}
                            {{--<strong class="h6 d-block text-warning mb-1">Front End Developer</strong>--}}
                            {{--<p class="text-secondary">Southern Street Floral Park, NY 11001</p>--}}
                        {{--</div>--}}
                        {{--<div class="work mb-4">--}}
                            {{--<strong class="h5 d-block text-secondary font-weight-bold mb-1">Blue Tech</strong>--}}
                            {{--<strong class="h6 d-block text-warning mb-1">Senior Programmer</strong>--}}
                            {{--<p class="text-secondary">George Avenue Mobile, AL 36608</p>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</section>--}}
            {{--</div>--}}
        </div>
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <h2 class="font-weight-bold m-0">
                    {!! ($user->fullname)? : 'null' !!}
                </h2>
                {{--<address class="m-0 pt-2 pl-0 pl-md-4 font-weight-light text-secondary">--}}
                    {{--<i class="fa fa-map-marker"></i>--}}
                    {{--Garden City, NY--}}
                {{--</address>--}}
            </div>
            <p class="h5 text-primary mt-2 d-block font-weight-light">
                @if($user->experience)
                    @if(isset($user->experience[0]['position']))
                        {!! $user->experience[0]['position'] !!}
                    @endif
                @endif
            </p>
            {{--<p class="lead mt-4">All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet.</p>--}}
            {{--<section class="mt-5">--}}
                {{--<h3 class="h6 font-weight-light text-secondary text-uppercase">Rankings</h3>--}}
                {{--<div class="d-flex align-items-center">--}}
                    {{--<strong class="h1 font-weight-bold m-0 mr-3">4.85</strong>--}}
                    {{--<div>--}}
                        {{--<input data-filled="fa fa-2x fa-star mr-1 text-warning" data-empty="fa fa-2x fa-star-o mr-1 text-light" value="5" type="hidden" class="rating" data-readonly />--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</section>--}}
            {{--<section class="d-flex mt-5">--}}
                {{--<button class="btn btn-light bg-transparent mr-3 mb-3">--}}
                    {{--<i class="fa fa-comments"></i>--}}
                    {{--Private Message--}}
                {{--</button>--}}
                {{--<button class="btn btn-light bg-transparent mr-3 mb-3">--}}
                    {{--<i class="fa fa-warning"></i>--}}
                    {{--Report User--}}
                {{--</button>--}}
                {{--<button class="btn btn-primary mb-3">--}}
                    {{--<i class="fa fa-check"></i>--}}
                    {{--Hire Me--}}
                {{--</button>--}}
            {{--</section>--}}
            <section class="mt-4">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">
                            About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="skills-tab" data-toggle="tab" href="#skills" role="tab" aria-controls="skills" aria-selected="false">
                            Skills
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="education-tab" data-toggle="tab" href="#education" role="tab" aria-controls="education" aria-selected="false">
                            Education
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="experience-tab" data-toggle="tab" href="#experience" role="tab" aria-controls="experience" aria-selected="false">
                            Experience
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="languages-tab" data-toggle="tab" href="#languages" role="tab" aria-controls="languages" aria-selected="false">
                            Languages
                        </a>
                    </li>
                </ul>
                <div class="tab-content py-4" id="myTabContent">
                    <div class="tab-pane py-3 fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <h6 class="text-uppercase font-weight-light text-secondary">
                            Contact Information
                        </h6>
                        <dl class="row mt-4 mb-4 pb-3">

                            @if($user->email)
                                <dt class="col-sm-3">Email address</dt>
                                <dd class="col-sm-9">
                                    <a href="{!! 'mailto:'.$user->email !!}">{!! $user->email !!}</a>
                                </dd>
                            @endif

                            @if($user->phone)
                                <dt class="col-sm-3">Phone</dt>
                                <dd class="col-sm-9">{!! $user->phone !!}</dd>
                            @endif

                            @if($user->address)
                                <dt class="col-sm-3">Home address</dt>
                                <dd class="col-sm-9">
                                    <address class="mb-0">{!! $user->address !!}</address>
                                </dd>
                            @endif

                            @if($user->linkedin)
                                <dt class="col-sm-3">LinkedIn</dt>
                                <dd class="col-sm-9">
                                    <a href="{!! $user->linkedin !!}">{!! $user->linkedin !!}</a>
                                </dd>
                            @endif

                            @if($user->github)
                                <dt class="col-sm-3">Github</dt>
                                <dd class="col-sm-9">
                                    <a href="{!! $user->github !!}">{!! $user->github !!}</a>
                                </dd>
                            @endif
                        </dl>

                        <h6 class="text-uppercase font-weight-light text-secondary">
                            Basic Information
                        </h6>
                        <dl class="row mt-4 mb-4 pb-3">

                            @if($user->birthday)
                                <dt class="col-sm-3">Birthday</dt>
                                <dd class="col-sm-9">{!! $user->birthday !!}</dd>
                            @endif

                            @if($user->gender)
                                <dt class="col-sm-3">Gender</dt>
                                <dd class="col-sm-9">{!! $user->gender !!}</dd>
                            @endif

                            @if($user->nationality)
                                <dt class="col-sm-3">Nationality</dt>
                                <dd class="col-sm-9">{!! $user->nationality !!}</dd>
                            @endif
                        </dl>
                    </div>
                    <div class="tab-pane fade" id="skills" role="tabpanel" aria-labelledby="profile-tab">
                        <div>
                            @if($user->skills)
                                @foreach($user->skills as $skill)
                                    <h5 class="skill"><span class="badge badge-pill badge-success">{!! $skill !!}</span></h5>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="tab-pane fade" id="education" role="tabpanel" aria-labelledby="profile-tab">
                        <section class="mb-4 pb-1">
                            <div class="work-experience pt-2">
                                @if($user->education)
                                    @foreach($user->education as $education)
                                        <div class="work mb-4">
                                            <strong class="h5 d-block text-secondary font-weight-bold mb-1"><span class="label">School:</span> {!! $education['university'] !!}</strong>
                                            <strong class="h6 d-block text-warning mb-1"><span class="label">Degree:</span> {!! $education['degree'] !!}</strong>
                                            <p class="text-secondary"><span class="label">Period:</span> {!! $education['date'] !!}</p>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </section>
                    </div>
                    <div class="tab-pane fade" id="experience" role="tabpanel" aria-labelledby="contact-tab">
                        <section class="mb-4 pb-1">
                            <div class="work-experience pt-2">
                                @if($user->experience)
                                    @foreach($user->experience as $experience)
                                        <div class="work mb-4">
                                            <strong class="h5 d-block text-secondary font-weight-bold mb-1"><span class="label">Company:</span> {!! $experience['company'] !!}</strong>
                                            <strong class="h6 d-block text-warning mb-1"><span class="label">Position:</span> {!! $experience['position'] !!}</strong>
                                            <p class="text-secondary"><span class="label">Period:</span> {!! $experience['date'] !!}</p>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </section>
                    </div>
                    <div class="tab-pane fade" id="languages" role="tabpanel" aria-labelledby="profile-tab">
                        <div>
                            @if($user->languages)
                                @foreach($user->languages as $language)
                                    <h5 class="skill"><span class="badge badge-pill badge-success">{!! $language !!}</span></h5>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endif

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-rating/1.5.0/bootstrap-rating.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/easy-pie-chart/2.1.6/jquery.easypiechart.min.js"></script>
<script type="text/javascript" src="/js/custom.js"></script>
</body>
</html>