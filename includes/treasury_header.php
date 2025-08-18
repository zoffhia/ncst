<?php 
    include(__DIR__ . '/../functions/saving_session.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCST Enrollment System</title>
    <link rel="icon" href="/ncst/img/ncst-edu-icon.png">
    <script src="/ncst/js/tailwind-css.js"></script>
    <script src="/ncst/js/flowbite.min.js"></script>
    <link rel="stylesheet" href="/ncst/css/custom_animation.css">
    <link rel="stylesheet" href="/ncst/css/sweetalert2.min.css">
    <script src="/ncst/js/sweetalert2.min.js"></script>
    <script src="/ncst/js/logout_vue.js"></script>
</head>
<body class="bg-gray-200">

<nav class="fixed top-0 z-50 w-full bg-blue-950 shadow-lg">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="absolute right-0 top-0 h-full w-24 mask-l-from-50% mask-l-to-90% bg-[url('/ncst/img/border-h1.png')] bg-repeat bg-center opacity-60"></div>

            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start rtl:justify-end">
                <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-100 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                    </svg>
                </button>
                <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
                    <img src="/ncst/img/NCST-logo.png" class="h-12" alt="NCST Logo" />
                    <span class="text-white font-semibold
                 text-xs xs:text-sm sm:text-base md:text-lg lg:text-xl
                 leading-tight">
                        National College of Science & Technology
                    </span>
                </a>
                </div>

                <div class="flex items-center">
                    <div class="flex items-center ms-3">
                        <div>
                        <button type="button" class="flex text-sm bg-white rounded-full focus:ring-4 focus:ring-gray-300" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                            <span class="sr-only">Open user menu</span>
                        </button>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</nav>
      
<aside id="logo-sidebar" class="flex flex-col fixed top-0 left-0 z-40 w-64 h-screen pt-20 bg-blue-950 border-r border-gray-700 transition-transform -translate-x-full sm:translate-x-0">
    <div class="h-full overflow-y-auto bg-blue-950 flex flex-col">  
        <!-- Profile -->
      ` <div class="flex flex-col items-center py-4 border-b border-gray-700">
            <div class="flex justify-center items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="text-white" width="100" height="100" viewBox="0 0 32 32" fill="currentColor">
                    <path d="M16 2C8.268 2 2 8.268 2 16s6.268 14 14 14s14-6.268 14-14S23.732 2 16 2m0 22.5c-3.866 0-7-2.429-7-6.071A2.43 2.43 0 0 1 11.429 16h9.142A2.43 2.43 0 0 1 23 18.429c0 3.642-3.134 6.071-7 6.071m0-10A3.75 3.75 0 1 1 16 7a3.75 3.75 0 0 1 0 7.5"/>
                </svg>
            </div>

            <h2 class="text-lg font-semibold text-white">Treasury Officer's Full Name</h2>
            <p class="text-sm text-gray-300">Email Address</p>
            <hr>
        </div>
      
        <!-- Menu -->
        <ul class="space-y-2 font-medium mt-4 flex-1">
            <li>
                <a href="/ncst/portals/treasury_portal.php" class="flex items-center justify-start gap-2 text-white p-3 rounded-lg hover:bg-blue-800">
                    <svg class="ml-3" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 28 28"><path fill="currentColor" d="M5.754 5a3.75 3.75 0 0 0-3.75 3.75v.75H26v-.75A3.75 3.75 0 0 0 22.25 5zm-3.75 14.25V11H26v8.25A3.75 3.75 0 0 1 22.25 23H5.755a3.75 3.75 0 0 1-3.75-3.75M18.25 16.5a.75.75 0 0 0 0 1.5h3.5a.75.75 0 0 0 0-1.5z"/></svg>
                    <span>Payments</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center justify-start gap-2 text-white p-3 rounded-lg hover:bg-blue-800">
                <svg class="ml-3" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M14 8V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-2"/><path d="M9 12h12l-3-3m0 6l3-3"/></g></svg>
                    <span>Logout</span>
                </a>
            </li>
        </ul>

    </div>
</aside>