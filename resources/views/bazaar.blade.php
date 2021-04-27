@extends('layout')
@section('content')
<style>
    section.wholetabs {
        width: 90%;
        margin: auto;
    }

    .dinebtn {
        border-radius: 15px !important;
        border: 2px solid white;
        text-transform: none;
        line-height: 35px;
    }

    .hero-subtitle-dine {
        font-size: 30px;
        font-weight: lighter;
        padding: 0;
        margin: 0;
        line-height: 120%;
        color: white;
    }

    .owl-pagination {
        bottom: 10px !important;
        text-align: right;
        width: 98%;
    }

    /* Set height of body and the document to 100% to enable "full page tabs" */
    body,
    html {
        height: 100%;
        margin: 0;
        font-family: Arial;
    }

    /* Style tab links */
    .tablink {
        background-color: #b9b9b9;
        color: white;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        font-size: 17px;
        width: 25%;
        box-shadow: 0;
    }

    .tablink:hover {
        background-color: #777;
    }

    /* Style the tab content (and add height:100% for full page content) */
    .tabcontent {
        color: white;
        display: none;
        height: 100%;
    }

    #Home {
        background-color: red;
    }

    #News {
        background-color: green;
    }

    #Contact {
        background-color: blue;
    }

    #About {
        background-color: orange;
    }

    .product-info {
        padding: 0 !important;
    }

    .post-title {
        color: black;
        font-weight: 400 !important;
    }

    .featsubtitle {
        color: green;
    }

    .delicacy-shop-logo {
        filter: brightness(0) invert(1);
    }

    .category {
        font-weight: lighter;
        color: black
    }

    .category-active {
        color: #288248;
        text-decoration: underline;
        text-decoration-thickness: 2px;
    }

    .frame {
        width: 250px;
        height: 250px;
        vertical-align: middle;
        text-align: center;
        display: table-cell;
    }

    .imgz {
        max-width: 95%;
        max-height: 90%;
        display: block;
        margin: 0 auto;
        min-width: 95% !important;
    }

    .shoplistmobile {
        justify-content: space-evenly;
        width: 100%;
        display: flex;
        background: #b9b9b9;
        margin-top: 20px;
        flex-wrap: wrap;
        height: 60px;
    }

    .shoplistmobile>button {
        flex: 10%;
        height: 100% !important;
    }

    .mobiletabs {
        display: none;
    }

    .divitext {
        color: white;
        font-size: 24px;
        font-weight: 100;
        font-family: 'Avenir';
    }

    .buttonsshow {
        background: green;
        height: 30px;
        width: 80%;
        text-align: center;
        margin: auto;
        margin-bottom: 20px;
    }

    @media only screen and (max-width: 600px) {
        .catmobile {
            font-size: 16px;
        }

        .tabprod {
            display: block !important;
        }

        .frame {
            width: 400px;
        }

        .wholemobile {
            width: 100% !important;
        }

        .tablinkprod {
            width: 100% !important;
        }

        .heading-banner-area {
            margin: 0 !important;
        }

        .buttonmobile {
            height: 60px;
        }


        .shoplistmobile>button {
            flex: 25%;
            height: 100% !important;
        }

        .shoplistmobile {
            height: 100%;
        }

        .mobiletabs {
            display: block;

        }

        .shoplistmobiletabs>button {
            flex: 100%;
            height: 100% !important;
        }

        .owlmobile {
            height: 100% !important;
        }

        .dinebtn {
            width: 100% !important;
        }

        section.wholetabs {
            width: 100%;
            margin: auto;
        }
    }
</style>
<link rel="stylesheet" href="css/hurst.css">
<div class="heading-banner-area overlay-bg" style="background: url('img/bazaar/clothing.jpg') no-repeat scroll center center / cover;margin: 0 5%;">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="heading-banner">
                    <div class="heading-banner-title">
                        <h2 style="font-weight:lighter;font-size:60px;padding: 100px 0 100px;">Clothings</h2>
                    </div>
                    <div class="breadcumbs pb-15">
                        <ul>
                            <li><a href="index.html" style="font-weight:lighter;">Home</a></li>
                            <li style="font-weight:lighter;">Plenty bazaar</li>
                            <li style="font-weight:lighter;" id="breadcrumbshopname">Clothings</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>





<script>
</script>
<div style="border-top: 2px solid #b2bad4;margin-top: 30px;">
    @include('footer')
</div>

@endsection