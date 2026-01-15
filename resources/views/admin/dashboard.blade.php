@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Dashboard</h1>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>150</h3>
                        <p>New Orders</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
