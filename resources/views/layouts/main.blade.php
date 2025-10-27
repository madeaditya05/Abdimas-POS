<!DOCTYPE html>
<html lang="en">

@include('layouts.bagian.header')

<body>
  <div class="container-scroller">
    
    @include('layouts.bagian.navbar')
    
    <div class="container-fluid page-body-wrapper">
      
      @include('layouts.bagian.setting')
      
      @include('layouts.bagian.sidebar')
      
      <div class="main-panel">
        <div class="content-wrapper">
          
          @yield('content')

        </div>
        @include('layouts.bagian.footer')

      </div>
      </div>   
    </div>
  @include('layouts.bagian.script')

</body>

</html>