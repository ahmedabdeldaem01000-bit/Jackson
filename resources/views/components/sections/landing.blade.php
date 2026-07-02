@push('styles')

<style>
    .heroSwiper{
    width:100%;
    height:100vh;
        background:black;
}

.heroSwiper .swiper-wrapper  .swiper-slide{
    position:relative;
    overflow:hidden;
     background:black;
}
.heroSwiper .swiper-wrapper .swiper-slide img{
    width:100%;
    height:100%;
    object-fit:cover;

    transform: scale(1);
    transition: transform 5s linear;
}
.heroSwiper .swiper-wrapper .swiper-slide-active img{
    animation: zoomEffect 6s linear forwards;
}

@keyframes zoomEffect{
    from{
        transform: scale(1);
    }

    to{
        transform: scale(1.15);
    }
}

.heroSwiper .swiper-wrapper .swiper-slide .overlay{

position:absolute;
inset:0;

background:
linear-gradient(
to right,
rgba(0,0,0,.75),
rgba(0,0,0,.35)
);

}

.content{
    position:absolute;
    top:50%;
    right:8%;
    transform:translateY(-50%);
    z-index:10;

    width:90%;
    max-width:650px;

    color:#fff;
    text-align:right;
}
.content h1{
    font-size:clamp(2rem,5vw,4rem);
    line-height:1.2;
    margin-bottom:15px;
}
.content p{

    font-size:clamp(1rem,2vw,1.3rem);
    line-height:1.7;

}

.heroSwiper .swiper-button-next,
.heroSwiper .swiper-button-prev{

    color:white;

}
@media (max-width:768px){

.content{

right:5%;
left:5%;
text-align:center;

}

.content h1{

margin-bottom:10px;

}

.content p{

max-width:100%;

}

}
</style>
 @endpush

     <div class="bg-black swiper heroSwiper">

    <div class="bg-black swiper-wrapper">

        <div class="swiper-slide">

            <img src="{{ asset('images/2.jpg') }}" alt="">

            <div class="overlay"></div>

            <div class="content">
                <h1 class="heading-font">حلاقة راقية تعكس ذوقك</h1>
                <p>
                    خبرة تُحسّن ملامحك وتمنحك ثقةً لا تُقاوم في كل لحظة.
                </p>
            </div>

        </div>

        <div class="swiper-slide">

            <img src="{{ asset('images/1.jpg') }}" alt="">

            <div class="overlay"></div>

            <div class="content">
                <h1 class="heading-font">احجز موعدك في ثوانٍ</h1>
                <p>
                    تجربة حجز سلسة، مريحة، ومصممة لتناسب أسلوب حياتك.
                </p>
            </div>

        </div>

    </div>

    <div class="w-48 rounded-full border border-white p-8 text-white swiper-button-next"></div>

    <div class="w-48 rounded-full border border-white p-8 text-white swiper-button-prev"></div>

</div>
 