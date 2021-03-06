@extends('layout')
@section('content')
<style>
    .frame {
        width: 250px;
        height: 250px;
        vertical-align: middle;
        text-align: center;
        display: table-cell;
    }

    .imgz {
        max-width: 100%;
        max-height: 100%;
        display: block;
        margin: 0 auto;
    }

    .ssproduct {
        margin: 0 5px;
    }

    .featicons {
        width: 25px !important;
    }

    @media only screen and (max-width: 600px) {
        .contu {
            width: 95%;
        }
    }
</style>
<section class="page-title text-center bg-light">
    <div class="container relative clearfix">
        <div class="title-holder">
            <div class="title-text">
                <h1 style="text-transform: uppercase;font-family:'Avenir Bold'">Search results for "{{$item}}"</h1>

            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="newsletter" id="subscribe" style="background-color:white;border:0;">
    <div class="container">
        <div class="row">
            <div class="col-sm-12 text-center">

            </div>
            <div class="col-sm-12 text-center">
                <h4 style="text-transform: uppercase;font-family:'Avenir Bold'">Search for</h4>
                <form class="relative newsletter-form" onsubmit="searchItem(event)" id="search-form">
                    <input type="text" class="newsletter-input" name="search-item" id="search-item" value="{{$item}}" style="border: 2px solid #001b71;font-size:14px;">
                    <input type="submit" class="btn btn-lg btn-dark newsletter-submit" value="Search" style="font-weight:500;font-size:14px">
                </form>
            </div>
        </div>
    </div>
</section>s


<div class="purchase-online-area ">
    <div class="container contu">
        <div class="row">

        </div>
        <div class="row">
            <div class="col-lg-12 text-center">
                <!-- Nav tabs -->

            </div>
            <div class="col-lg-12">
                <!-- Tab panes -->
                <div class="-">
                    <div class="tab-pane active">
                        <section style="margin: auto;width:90%;text-align:center;" id="fav-product-panel">
                            @if(isset($products))
                            @foreach($products as $product)
                            <div class="producthover single-product col-lg-3 col-xs-6 hidden-md hidden-sm " style="margin-bottom:30px;">
                                <div class="product-img frame"><a href="product/{{$product->product_id}}"><img src="storage/products/{{$product->url}}" alt="" loading="lazy" class="imgz"></a>
                                    <div class="fix buttonsshow" style="visibility: visible;"><span class="pro-price "><img class="featicons" src="img/nav/bag.png" loading="lazy" style="width:25px;min-width:25px;filter: brightness(0) invert(1);"></span>
                                        <span class="pro-rating "><img class="featicons" src="img/nav/search.png" loading="lazy" style="width:25px;min-width:25px;filter: brightness(0) invert(1);"></span></div>
                                    <div class="product-action clearfix"></div>
                                </div>
                                <div class="product-info clearfix">
                                    <div class="fix">
                                        <h4 class="post-title floatcenter feattitle"><a href="product/{{$product->product_id}}" style="">{{$product->name_en}} </a></h4>
                                        <p class="floatcenter hidden-sm featsubtitle  post-title">SAR {{$product->price}}</p>
                                    </div>
                                    <div class="fix featlineicons">
                                        <span class="pro-price floatleft" onclick="MakeFavourite({{$product->product_id}})"><img class="featicons" src="img/nav/fav.png" loading=lazy>
                                        </span>
                                        </a>
                                        <a href="{{ url('/product/' . $product->id) }}"><span class="pro-rating floatright">
                                                <img class="featicons" src="img/nav/bag.png" loading=lazy>
                                            </span>
                                        </a>
                                    </div>

                                </div>

                            </div>
                            @endforeach
                            @endif
                        </section>

                    </div>


                </div>
            </div>
        </div>
    </div>
</div>








<script>
    $(document).ready(function() {
        $(".promo-inner").hover(function() {
            $(this).siblings(".categoverlay").css("opacity", "0.2");
        }, function() {
            $(this).siblings(".categoverlay").css("opacity", "0.6");
            ph
        });
        $(".categoverlay").hover(function() {
            $(this).css("opacity", "0.2");
        }, function() {
            $(this).css("opacity", "0.6");
        });

        $('.featlineicons').css({
            'visibility': 'hidden'
        });
        $(".single-product").hover(function() {
            $(this).children(".product-info").children(".featlineicons").css({
                'visibility': 'visible'
            });
        }, function() {
            $(this).children(".product-info").children(".featlineicons").css({
                'visibility': 'hidden'
            });
        });


        $(".brand-slide").hover(function() {
            $(this).css({
                'transform': 'scale(1.2)'
            });
        }, function() {
            $(this).css({
                'transform': 'scale(1)'
            });
        });
    });

    function searchItem(e) {
        e.preventDefault();
        const form = new FormData(document.getElementById('search-form'))
        var base_url = $('meta[name=base_url]').attr('content');
        if (form.get('search-item').trim().length > 0) {
            window.location.replace(base_url + 'search/' + form.get('search-item').trim());
        }
    }
</script>
<div style="border-top: 2px solid #b2bad4;margin-top: 30px;">
    @include('footer')
</div>
@endsection