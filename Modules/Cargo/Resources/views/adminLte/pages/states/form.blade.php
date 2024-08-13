@csrf

<div class="form-group">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" class="form-control" placeholder="Enter state name">
    @error('name')
    <div class="is-invalid"></div>
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
</div>
<div class="form-group" hidden>
    <label for="country_id">Country ID:</label>
    <input type="number" name="country_id" id="country_id" class="form-control" placeholder="Enter country ID" value="{{$country_id}}" hidden>
</div>
<div class="form-group">
    <label for="country_code">Country Code:</label>
    <input type="text" name="country_code" id="country_code" class="form-control" placeholder="Enter country code">
    @error('country_code')
        <div class="is-invalid"></div>
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="form-group">
    <label for="fips_code">FIPS Code:</label>
    <input type="text" name="fips_code" id="fips_code" class="form-control" placeholder="Enter FIPS code">
    @error('fips_code')
        <div class="is-invalid"></div>
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="form-group">
    <label for="iso2">ISO2:</label>
    <input type="text" name="iso2" id="iso2" class="form-control" placeholder="Enter ISO2">
    @error('iso2')
        <div class="is-invalid"></div>
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="form-group">
    <label for="latitude">Latitude:</label>
    <input type="text" name="latitude" id="latitude" class="form-control" placeholder="Enter latitude">
    @error('latitude')
        <div class="is-invalid"></div>
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<div class="form-group">
    <label for="longitude">Longitude:</label>
    <input type="text" name="longitude" id="longitude" class="form-control" placeholder="Enter longitude">
    @error('longitude')
        <div class="is-invalid"></div>
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
