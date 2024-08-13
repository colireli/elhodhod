

@php
$textColor   = array_key_exists('text_color', $data) && $data['text_color'] ? "color: {$data['text_color']} !important;" :  '' ;
$titleColor  = array_key_exists('title_color', $data) && $data['title_color'] ? "color: {$data['title_color']} !important;" :  '' ;

@endphp

@if(array_key_exists('display', $data) && $data['display'])

    <div class="container container-popular" style="{{ array_key_exists('header_bg', $data) && $data['header_bg'] ? "background-color: {$data['header_bg']} !important;" :  ''  }}" >
        <div class=" aos-init aos-animate" data-aos="fade-up">
        <h2 class="section-title">
            <span style="{{ $titleColor }}" > {{$data['section_title'][app()->getLocale()] ?? ''}} </span>
        </h2>

        <div class="section-caption">
            <span class="light" style="{{ $textColor }}" > {{$data['section_description'][app()->getLocale()] ?? ''}}  </span>
        </div>
        </div>
    </div>

@endif

<style>

        @media (max-width: 425px)
        {
            .container-popular .section-title span{
                font-size: 20px;
            }

           #supercharge .info .heading{
                font-size: 24px;
            }

            #supercharge .info {
                max-width: 70%;
                margin: auto;
                text-align: center;
            }  


            div.full-width.help .section-caption span {
                font-size: 15px;
            }


            .section-title{
                padding: 0px 10px !important;
            }

            
        }  
</style>