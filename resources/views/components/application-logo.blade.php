@php
  $rolename = Auth::user()->getRoleNames()->first();
  $arr = [
      'admin' => 'admin',
      'arcade-owner' => 'arcades',
      'arcade-staff' => 'arcades',
      'machine-owner' => 'machine',
      'machine-manager' => 'machine',
      'member' => '',
      'user' => ''
  ];
  $route = (empty($rolename)) ? 'dashboard' : $arr[$rolename].'.dashboard';
@endphp

<a href="{{ route($route) }}">
  <svg 
      {{ $attributes->merge(['viewBox' => '0 0 200 200']) }} 
      xmlns="http://www.w3.org/2000/svg">
      <circle cx="100" cy="100" r="90" stroke="gray" stroke-width="15" fill="none" />
      <polygon points="100,20 170,140 30,140" stroke="gray" stroke-width="15" fill="none" />
      <polygon points="100,180 170,60 30,60" stroke="gray" stroke-width="15" fill="none" />
  </svg>
</a>