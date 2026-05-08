<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Vali is a responsive and free dashboard theme/template built with Bootstrap 5, SASS and PUG.js.">
    <!-- Twitter meta-->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:site" content="@pratikborsadiya">
    <meta property="twitter:creator" content="@pratikborsadiya">
    <!-- Open Graph Meta-->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Vali Admin">
    <meta property="og:title" content="">
    <meta property="og:url" content="http://pratikborsadiya.in/vali-admin">
    <meta property="og:image" content="http://pratikborsadiya.in/blog/vali-admin/hero-social.png">
    <meta property="og:description" content="Vali is a responsive and free dashboard theme/template built with Bootstrap 5, SASS and PUG.js.">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('backend-assets/css/main.css') }}">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body class="app sidebar-mini">
    @includeIf('backend.layouts.partial.header')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="bi bi-speedometer"></i> Dashboard</h1>
                <p>A free and open source Bootstrap 5 admin template</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="bi bi-house-door fs-6"></i></li>
                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
            </ul>
        </div>

        @yield('content')
    </main>


    <!-- Essential javascripts for application to work-->
    <script src="{{ asset('backend-assets/css/main.css') }}"></script>
    <script src="{{ asset('backend-assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('backend-assets/js/main.js') }}"></script>
    <!-- Page specific javascripts-->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script type="text/javascript">
        const salesData = {
            xAxis: {
                type: 'category',
                data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    formatter: '${value}'
                }
            },
            series: [{
                data: [150, 230, 224, 218, 135, 147, 260],
                type: 'line',
                smooth: true
            }],
            tooltip: {
                trigger: 'axis',
                formatter: "<b>{b0}:</b> ${c0}"
            }
        }

        const supportRequests = {
            tooltip: {
                trigger: 'item'
            },
            legend: {
                orient: 'vertical',
                left: 'left'
            },
            series: [{
                name: 'Support Requests',
                type: 'pie',
                radius: '50%',
                data: [{
                        value: 300,
                        name: 'In Progress'
                    },
                    {
                        value: 50,
                        name: 'Delayed'
                    },
                    {
                        value: 100,
                        name: 'Complete'
                    }
                ],
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }]
        };

        const salesChartElement = document.getElementById('salesChart');
        const salesChart = echarts.init(salesChartElement, null, {
            renderer: 'svg'
        });
        salesChart.setOption(salesData);
        new ResizeObserver(() => salesChart.resize()).observe(salesChartElement);

        const supportChartElement = document.getElementById("supportRequestChart")
        const supportChart = echarts.init(supportChartElement, null, {
            renderer: 'svg'
        });
        supportChart.setOption(supportRequests);
        new ResizeObserver(() => supportChart.resize()).observe(supportChartElement);
    </script>
    <!-- Google analytics script-->
    <script type="text/javascript">
        if (document.location.hostname == 'pratikborsadiya.in') {
            (function(i, s, o, g, r, a, m) {
                i['GoogleAnalyticsObject'] = r;
                i[r] = i[r] || function() {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
                a = s.createElement(o),
                    m = s.getElementsByTagName(o)[0];
                a.async = 1;
                a.src = g;
                m.parentNode.insertBefore(a, m)
            })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
            ga('create', 'UA-72504830-1', 'auto');
            ga('send', 'pageview');
        }
    </script>
</body>

</html>