import Swiper from 'swiper';
import { Navigation, EffectFade, Autoplay } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/effect-fade';

new Swiper('.heroSwiper', {
    modules: [Navigation, EffectFade, Autoplay],

    effect: 'fade',

    fadeEffect: {
        crossFade: true,
    },

    autoplay: {
        delay: 5000,
        disableOnInteraction: false,
    },

    loop: true,

    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },

    speed: 1500,
});


new Swiper(".testimonialSwiper", {
    modules: [Navigation, Autoplay],

    slidesPerView: 1,

    loop: true,

    navigation: {
        nextEl: ".testimonial-next",
        prevEl: ".testimonial-prev",
    },

    autoplay: {
        delay: 5000,
    },
});

// const navbar = document.getElementById('navbar');
const about = document.getElementById('about');
const login = document.getElementById('login');
// const list = document.getElementById('list');



const appointment = document.getElementById('appointment');

const observer = new IntersectionObserver(
    ([entry]) => {

        if (entry.target.id === "appointment") {

            if (entry.isIntersecting) {
                appointment.classList.add("active");
            } else {
                appointment.classList.remove("active");
            }

        }

    },
    {
        threshold: 0.3,
    }
);

if (appointment) {
    observer.observe(appointment);
}

if (about) {
    observer.observe(about);
}


//  const menuBtn = document.getElementById("menuBtn");
const mobileMenu = document.getElementById("mobileMenu");

const navbar = document.getElementById("navbar");
const navbarContainer = document.querySelector(".navbar-container");
const logo = document.querySelector(".logo");
const list = document.getElementById("list");
const menuBtn = document.getElementById("menuBtn");


menuBtn.addEventListener("click", () => {

    mobileMenu.classList.toggle("hidden");

});
window.addEventListener("scroll", () => {

    if (window.scrollY > 80) {

        navbar.classList.add(
            "bg-white/95",
            "backdrop-blur-md",
            "shadow-lg"
        );

        navbar.classList.remove("bg-transparent");

        navbarContainer.classList.remove("py-7");
        navbarContainer.classList.add("py-3");

        logo.classList.remove("h-20");
        logo.classList.add("h-14");

        list.classList.remove("text-white");
        list.classList.add("text-black");

        menuBtn.classList.remove("text-white");
        menuBtn.classList.add("text-black");

    } else {

        navbar.classList.remove(
            "bg-white/95",
            "backdrop-blur-md",
            "shadow-lg"
        );

        navbar.classList.add("bg-transparent");

        navbarContainer.classList.remove("py-3");
        navbarContainer.classList.add("py-7");

        logo.classList.remove("h-14");
        logo.classList.add("h-20");

        list.classList.remove("text-black");
        list.classList.add("text-white");

        menuBtn.classList.remove("text-black");
        menuBtn.classList.add("text-white");

    }

});


const prices = {

    shaves:{

        title:"الحلاقة الساخنة",

        items:[

            {
                name:"حلاقة توقيعية كاملة",
                duration:"مدة الخدمة / ٦٠ دقيقة",
                price:"٨٠ دولار"
            },

            {
                name:"جلسة عناية فاخرة",
                duration:"مدة الخدمة / ٣٠ دقيقة",
                price:"٢٥ دولار"
            },

            {
                name:"حلاقة ساخنة مميزة",
                duration:"مدة الخدمة / ٣٠ دقيقة",
                price:"٢٥ دولار"
            },

            {
                name:"حلاقة ساخنة توقيعية",
                duration:"مدة الخدمة / ٣٠ دقيقة",
                price:"٥٠ دولار"
            },

            {
                name:"حلاقة بالشفرة الرفيعة",
                duration:"مدة الخدمة / ٣٠ دقيقة",
                price:"١٥ دولار"
            }

        ]

    },

    haircuts:{

        title:"التصفيف والقص",

        items:[

            {
                name:"قص كلاسيكي أنيق",
                duration:"٤٥ دقيقة",
                price:"٤٠ دولار"
            },

            {
                name:"قص فيد راقي",
                duration:"٤٠ دقيقة",
                price:"٣٥ دولار"
            },

            {
                name:"سكين فيد مُحكم",
                duration:"٥٠ دقيقة",
                price:"٤٥ دولار"
            }

        ]

    },

    trims:{

        title:"تقليم اللحية",

        items:[

            {
                name:"تقليم اللحية بدقة",
                duration:"٢٠ دقيقة",
                price:"٢٠ دولار"
            },

            {
                name:"تقليم لحيّة ساخنة",
                duration:"٣٠ دقيقة",
                price:"٣٠ دولار"
            }

        ]

    }

};

const title = document.getElementById("priceTitle");

const list2 = document.getElementById("priceList");

function render(tab){

    title.innerHTML = prices[tab].title;

    list2.innerHTML="";

    prices[tab].items.forEach(item=>{

        list2.innerHTML+=`

        <div class="price-row">

            <div class="price-info">

                <h4>${item.name}</h4>

                <p>${item.duration}</p>

            </div>

            <div class="price">

                ${item.price}

            </div>

        </div>

        `;

    });

}

render("shaves");

document.querySelectorAll(".price-tab").forEach(tab=>{

    tab.onclick=()=>{

        document.querySelector(".price-tab.active")?.classList.remove("active");

        tab.classList.add("active");

        render(tab.dataset.tab);

    }

});