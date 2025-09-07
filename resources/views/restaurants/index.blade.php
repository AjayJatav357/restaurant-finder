<!DOCTYPE html>
<html>
<head>
    <title>Indian Restaurant Finder</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <style>
        body { font-family: Arial; padding: 20px; }
        input, button { padding: 6px; margin-right: 5px; }
        #map { height: 500px; margin-top: 20px; }
        ul { list-style: none; padding: 0; }
        li { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h2>Find Restaurants in India</h2>

    <form method="POST" action="{{ route('restaurants.fetch') }}">
        @csrf
        <input type="text" name="city" placeholder="Enter city" value="{{ old('city') }}" required>
        <button type="submit">Search</button>
    </form>

    @error('city')
        <p style="color:red;">{{ $message }}</p>
    @enderror

    @isset($restaurants)
        <h3>Restaurants in {{ $city }}</h3>
        <div id="map"></div>
        <ul>
            @foreach($restaurants as $r)
                <li>
                    <strong>{{ $r['name'] }}</strong><br>
                    {{ $r['address'] }}<br>
                    @if($r['phone']) Phone: {{ $r['phone'] }}<br>@endif
                    @if($r['website']) <a href="{{ $r['website'] }}" target="_blank">Website</a>@endif
                </li>
            @endforeach
        </ul>
    @endisset

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
    @isset($restaurants)
        var map = L.map('map').setView([{{ $lat }}, {{ $lon }}], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        var restaurants = @json($restaurants);

        restaurants.forEach(function(r) {
            if(r.lat && r.lon){
                L.marker([r.lat, r.lon]).addTo(map)
                 .bindPopup("<b>" + r.name + "</b><br>" + r.address);
            }
        });
    @endisset
    </script>
</body>
</html>
