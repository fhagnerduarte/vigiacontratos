<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">

<x-head />

<body>

    <x-sidebar />

    <main class="dashboard-main">

        <x-navbar />

        <div class="dashboard-main-body">

            <x-breadcrumb :title="isset($title) ? $title : ''" :subTitle="isset($subTitle) ? $subTitle : ''" />

            @yield('content')

        </div>

        <x-footer />

    </main>

    <x-script :script="isset($script) ? $script : ''" />

</body>
</html>
