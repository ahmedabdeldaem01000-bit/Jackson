@include('admin.layout.head')
@include('admin.layout.header')
@include('admin.layout.nav')
@include('admin.layout.sidebar')

<div class="content-wrapper">
 
  <section class="content">
  {{ $slot }}
  </section>
</div>
   @livewireScripts
   @include('admin.layout.footer')