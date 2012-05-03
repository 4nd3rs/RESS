<h1>Device detection</h1>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Capability</th>
        <th>Value</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Is mobile?</td>
        <td><?php echo $client->getDeviceCapability('ux_full_desktop') ? 'Nope' : 'Yep'   ?></td>
    </tr>
    <tr>
        <td>Device</td>
        <td><?php echo $client->getDeviceCapability('brand_name') . " "
            . $client->getDeviceCapability('model_name')
            . ($client->getDeviceCapability('marketing_name') ? " (" . $client->getDeviceCapability('marketing_name') . ")" : "")  ?></td>
    </tr>
    <tr>
        <td>Viewport width</td>
        <td><?php echo $client->getDeviceCapability('max_image_width')?></td>
    </tr>
    <tr>
        <td>Resolution width</td>
        <td><?php echo $client->getDeviceCapability('resolution_width')?></td>
    </tr>
    <tr>
        <td>Pointing method</td>
        <td><?php echo $client->getDeviceCapability('pointing_method')?></td>
    </tr>


    </tbody>
</table>