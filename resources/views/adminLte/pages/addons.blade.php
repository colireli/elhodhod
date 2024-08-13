<x-base-layout>
    <x-slot name="pageTitle">
        @lang('view.system_update')
    </x-slot>
    @if(isset(get_current_lang()['code']) && get_current_lang()['code'] == 'ar')
        <style>
            .addon .button {
                left: 31px;
            }
        </style>
    @else
        <style>
            .addon .button {
                right: 31px;
            }
        </style>
    @endif

    <style>
        .configure  {
    margin-top: 12px;
}

.addon a{
    color: #e69200;
}

.addon .page-content {
  display: grid;
  grid-gap: 1rem;
  padding: 1rem;
  max-width: 1334px;
  margin: 0 auto;
  font-family: var(--font-sans);
}
@media (min-width: 600px) {
  .addon .page-content {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (min-width: 800px) {
    .addon .page-content {
        grid-template-columns: repeat(4, 1fr);
    }
}

.addon .card {
  max-height: 347px;
  position: relative;
  display: flex;
  align-items: flex-end;
  overflow: hidden;
  padding: 1rem;
  width: 100%;
  text-align: center;
  color: whitesmoke;
  background-color: rgb(180, 169, 169);
  box-shadow: 0 1px 1px rgb(0 0 0 / 10%), 0 2px 2px rgb(0 0 0 / 10%), 0 4px 4px rgb(0 0 0 / 10%), 0 8px 8px rgb(0 0 0 / 10%), 0 16px 16px rgb(0 0 0 / 10%);
}

@media (min-width: 600px) {
  .addon .card {
    height: 438px;
  }
}

.addon .content {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  padding: 1rem;
  transition: transform var(--d) var(--e);
  z-index: 1;
  margin-top: 156px;
}
.addon .card {
  text-align: center;
}

.addon .title {
  max-width: 254px;
  width: 229px;
  margin: 7%;
  position: absolute;
  top: 104px;
  text-align: center;
  font-size: 1.3rem;
  font-weight: bold;
  line-height: 2.2;
  z-index: 10;
}

.addon .card {
  text-align: center;
}
.addon .copy {
  font-size: 1.125rem;
  font-style: italic;
  line-height: 1.35;
}

.addon .container  {
    margin-top: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}
.addon .container h2{
    text-align: center;
}

.page-content .card  .info{
  position: absolute;
  display: flex;
  width: 103%;
  align-items: center;
  flex-direction: row-reverse;
  bottom: 0px;
  z-index: 10;
  right: 0px;
}

.page-content .card  .info .configure ,
.page-content .card  .info .form-check-solid , .page-content .card  .info  .delet_cart {
  padding-top: 17px;
  padding-bottom: 11px;
  width: 50%;

}

.page-content .card  .info .delet_card{
  position: absolute !important;
  top: 0px !important;
  right: 0px !important;
  background-color:#e60023 !important;
}

.addon .card:before {
    background-position:center ;
    background-repeat: no-repeat;
    background-size: cover;
    width: 100%;
    height: 100%;
}

.addon .button {
  background-color: #009ef7;
  border: none;
  color: #eee !important;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  position:absolute;
  top: 69px;
}
.addon .img_addons{
  height: 86%;
  width: 100%;
  position: absolute;
  top: 0px;
  right: 0px;
}
.addon .addon_checked{
  visibility:hidden;
}

.page-content .card .info .delet_cart {
  position: absolute !important;
  bottom: 0px !important;
  border: none !important;
  right: 0px !important;
  color: #eee !important;
  font-size: 16px;
  font-weight: bold;
  left:0;
}

.page-content .card  .info .configure {
  color: #eee !important;
  font-size: 16px;
  font-weight: bold;
}

.addon .form-check .form-check-label {
  color: aliceblue;
  cursor: pointer;
}

    </style>
     <!-- Start Section Addon  -->
    <section class="addon">
        <!-- Button Add New Addons -->
        <a href="{{route('addon.upload')}}" class="button rounded">@lang('view.add_new_addons') </a>
        @if(isset($all_addons) && count($all_addons) != 0)
        <main class="page-content">
            <!-- Start Card Addon  -->
            @foreach ($all_addons as $addon)

            <form method="post" id="kt_form_1" action="{{route('delete.addons',$addon->id)}}">
                @csrf
                <div class="card">

                    <img class="img_addons" src="{{asset('storage/addons/'.$addon->image)}}"alt="logo" >
                    <!-- <h2 class="title">{{$addon->name}}</h2> -->
                    <div class="info">
                        @if(isset($addon->status))
                            @if($addon->status == 1 )
                             <a  class="configure bg-success " href="{{ Route::has($addon->slug.'-settings') ? route($addon->slug.'-settings') : 'false' }}">{{ $addon->status == 1 ? __('view.configure_now') : '' }} </a>
                            @else
                             <button id="confirm" class="delet_cart bg-danger" type="submit">@lang('view.delete')</button>
                            @endif
                            <div class="form-check form-check-solid form-switch fv-row mt-3 font-weight-bold @if(!$addon->status == 1) bg-success  @else bg-warning @endif">
                                <input class="addon_checked" id="{{ $addon->id }}"   type="checkbox" @if($addon->status != 0) checked @endif onchange="update_read_status(this)" value="{{ $addon->id }}"/>
                                <label for="{{ $addon->id }}" class="form-check-label">@if(!$addon->status == 1)  @lang('view.activate')  @else @lang('view.disable') @endif </label>
                            </div>
                        @endif
                        </div>
                </div>
            </form>
            @endforeach
                <!-- End Card Addon  -->
            </main>
        @else
        <div class="container">
            <script src="https://cdn.lordicon.com/qjzruarw.js"></script>
            <lord-icon src="https://cdn.lordicon.com/dimghest.json" trigger="hover"
                colors="outline:#121331,primary:#c76f16,secondary:#ebe6ef" style="width:250px;height:250px"></lord-icon>
            <h2>@lang('view.There_no_addons_here') <a href="https://codecanyon.net/user/bdaia/portfolio">@lang('view.from_here')</a></h2>
        </div>
        @endif
    </section>
     <!-- End Section Addon  -->


     <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" id="mi-modal">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <h4 class="modal-title h6">{{__('view.are_you_sure') }}</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" id="modal-btn-no">{{ __('view.no') }}</button>
                    <button type="button" class="btn btn-primary" id="modal-btn-yes">{{ __('view.yes') }}</button>
                </div>
            </div>
        </div>
    </div>

</x-base-layout>
    <script type="text/javascript">
        function update_read_status(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('postchangingstatus') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    Swal.fire({ position: 'top-end', icon: 'success', title: '{{__('view.you_just_change_status')}}', showConfirmButton: false, timer: 2000 })
                    location.reload();
                }
                else{
                    Swal.fire({ position: 'top-end', icon: 'error', title: 'Error Occurred, Please Try Again Later', showConfirmButton: false, timer: 2000 })
                    location.reload();
                }
            });
        }

        var modalConfirm = function(callback){

        $("#confirm").on("click", function(){
            $("#mi-modal").modal('show');
        });

        $("#modal-btn-yes").on("click", function(){
            callback(true);
            $("#mi-modal").modal('hide');
        });

        $("#modal-btn-no").on("click", function(){
            callback(false);
            $("#mi-modal").modal('hide');
        });
        };

        modalConfirm(function(confirm){
        if(confirm){
            //Acciones si el usuario confirma
            $("#confirm").html('deleting...');
            $('#confirm').prop('disabled', true);
            $( "#kt_form_1" ).submit();
        }
        });

    </script>

