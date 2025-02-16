<script>
function MapPixelStringType(type) {
    return type;
}
function MapPixelStringSubType(type) {
    return "";
}
function MapPixelStringSubTypeVersion(version) {
    return 1;
}
function GetPixelStringTiming() {
    return 0;
}
</script>

<?
include_once('co-pixelStrings.php');
?>


<script>


function pixelStringInputsAreSane()
{
	// FIXME
	return 1;
}

function okToAddNewPixelStringInput(type)
{
	if ($('#' + type + '_Output_0').length)
	{
		alert('ERROR: You already have a ' + type + ' output created.');
		return 0;
	}

	return 1;
}

function addPixelOutput()
{
	var type = $('#pixelOutputType').val();

	if (!okToAddNewInput(type))
	{
		return;
	}

	var str = "<div class='piPixelOutputTableWrapper'>\n";

	var protocols = '';
	var protocol = '';

	var portCount = 0;
	if (type == 'RPIWS281X')
	{
		portCount = 2;
		protocol = 'ws281x';
		protocols = 'ws281x';
	}
	else if (type == 'SPI-WS2801')
	{
		portCount = 1;
		protocol = 'ws2801';
		protocols = 'ws2801';
	}
	else if (type == 'spixels')
	{
		portCount = 16;

		// Can't use ws2801 for now until mailbox issue is resolved.
		// spixels includes its own copy of the mailbox code, but
		// ends up calling the copy from jgarff's rpi-ws281x library
		// since the functions have the same names.  Do we fork and
		// rename or patch and submit a pull request?
		//protocol = 'ws2801';
		//protocols = 'ws2801,apa102,lpd6803,lpd8806';
		protocol = 'apa102';
		protocols = 'apa102,lpd6803,lpd8806';
	}
	else if (type == 'X11PixelStrings')
	{
		portCount = 32;
		protocol = 'X11';
		protocols = 'X11';
	}
	str += "<div class='backdrop tableOptionsForm'>"
	str += '<div class="row">';
	str += '<div class="col-md-auto tableOptionsFormHeadingCol">';
	str += '<h3>' + type + ' Output</h3>';
	str += '</div>';
	str += '<div class="col-md-auto">';
	str += "<div class='backdrop-dark form-inline enableCheckboxWrapper'>";
	str += "<b>Output Enabled:</b> <input type='checkbox' id='" + type + "_Output_0_enable' checked>";
	str += '</div>';
	str += '</div>';
	str += '</div>';
	str += '</div>';
	str += '<small class="text-muted text-right pt-2 d-block">Press F2 to auto set the start channel on the next row.</small>';
    str += "<div class='fppTableWrapper'>" +
        "<div class='fppTableContents' role='region' aria-labelledby='" + type + "_Output_0' tabindex='0'>";
	str += "<table id='" + type + "_Output_0' type='" + type + "' ports='" + portCount + "' class='fppSelectableRowTable'>";
	str += pixelOutputTableHeader();
	str += "<tbody>";

	var id = 0; // FIXME if we need to handle multiple outputs of the same type
	var i = 0;
	for (i = 0; i < portCount; i++)
	{
		str += pixelOutputTableRow(type, protocols, protocol, id, i, 0, '', 1, 0, 1, 0, 'RGB', 0, 0, 100, "1.0");
	}

	str += "</tbody>";
	str += "</table>";
    str += "</div>";
    str += "</div>";
	str += "</div>";

	$('#pixelOutputs').append(str);

	$('#' + type + '_Output_0').on('mousedown', 'tr', function(event, ui) {
		$('#pixelOutputs table tr').removeClass('selectedEntry');
		$(this).addClass('selectedEntry');
		selectedPixelStringRowId = $(this).attr('id');
	});
}


function populatePixelStringOutputs(data)
{
	$('#pixelOutputs').html("");
    
    if ("channelOutputs" in data) {
        for (var i = 0; i < data.channelOutputs.length; i++)
        {
            var output = data.channelOutputs[i];

            var type = output.type;
            var str = "<div class='piPixelOutputTableWrapper'>\n";
            var protocols = '';
            var protocol = '';
			str += "<div class='backdrop tableOptionsForm'>"
			str += '<div class="row">';
			str += '<div class="col-md-auto tableOptionsFormHeadingCol">';
			str += '<h3>' + type + ' Output</h3>';
			str += '</div>';
			str += '<div class="col-md-auto">';
            if (type == 'RPIWS281X')
            {
                protocols = 'ws281x';

                if (protocol == '')
                    protocol = 'ws281x';
            }
            else if (type == 'SPI-WS2801')
            {
                protocols = 'ws2801';

                if (protocol == '')
                    protocol = 'ws2801';
            }
            else if (type == 'spixels')
            {
                protocols = 'ws2801,apa102,lpd6803,lpd8806';

                if (protocol == '')
                    protocol = 'ws2801';
            }
            else if (type == 'X11PixelStrings')
            {
                protocol = 'X11';
                protocols = 'X11';
            }
			str += "<div class='backdrop-dark form-inline enableCheckboxWrapper'>";
            str += "<b>Output Enabled:</b> <input type='checkbox' id='" + type + "_Output_0_enable'";

            if (output.enabled)
                str += " checked";

            str += ">";
			str += '</div>';
			str += '</div>';
			str += '</div>';
			str += '</div>';
			str += '<small class="text-muted text-right pt-2 d-block">Press F2 to auto set the start channel on the next row.</small>';

            str += "<div class='fppTableWrapper'>" +
                "<div class='fppTableContents' role='region' aria-labelledby='" + type + "_Output_0' tabindex='0'>";
            str += "<table id='" + type + "_Output_0' type='" + type + "' ports='" + output.outputCount + "' class='fppSelectableRowTable'>";
            str += pixelOutputTableHeader();
            str += "<tbody>";

            var id = 0; // FIXME if we need to handle multiple outputs of the same type

            for (var o = 0; o < output.outputCount; o++)
            {
                var port = output.outputs[o];

                if (port.protocol)
                    protocol = port.protocol;

                for (var v = 0; v < port.virtualStrings.length; v++)
                {
                    var vs = port.virtualStrings[v];

                    str += pixelOutputTableRow(type, protocols, protocol, id, o, v, vs.description, vs.startChannel + 1, vs.pixelCount, vs.groupCount, vs.reverse, vs.colorOrder, vs.nullNodes, vs.zigZag, vs.brightness, vs.gamma);
                }
            }


	    if (output.outputCount == 0) {
                str += pixelOutputTableRow(type, protocols, protocol, id, 0, 0, '', 1, 0, 1, 0, 'RGB', 0, 0, 100, "1.0");
	    }
	    if (output.outputCount < 2) {
		str += pixelOutputTableRow(type, protocols, protocol, id, 1, 0, '', 1, 0, 1, 0, 'RGB', 0, 0, 100, "1.0");
            }

            str += "</tbody>";
            str += "</table>";
            str += "</div>";
            str += "</div>";
			str += "</div>";

            $('#pixelOutputs').append(str);

            $('#' + type + '_Output_0').on('mousedown', 'tr', function(event, ui) {
                $('#pixelOutputs table tr').removeClass('selectedEntry');
                $(this).addClass('selectedEntry');
                selectedPixelStringRowId = $(this).attr('id');
            });
        }
    }
}

function loadPixelStringOutputs()
{
	$.getJSON("api/channel/output/co-pixelStrings", function(data) {
		populatePixelStringOutputs(data)
	});
}

function savePixelStringOutputs() {
    var postData = getPixelStringOutputJSON();
    
	$.post("api/channel/output/co-pixelStrings", JSON.stringify(postData)).done(function(data) {
		$.jGrowl("Pixel String Output Configuration Saved",{themeState:'success'});
		SetRestartFlag(1);
	}).fail(function() {
		DialogError("Save Pixel String Outputs", "Save Failed");
	});
}

$(document).ready(function() {
	loadPixelStringOutputs();
	$('#addPixelOutputTypeSelector>a').click(function(e){
		$('#pixelOutputType').val($(this).data('value'));
		addPixelOutput();
	})
});

</script>

<div id='tab-PixelStrings'>
	<div id='divPixelStrings'>

        <div class="row tablePageHeader">
			<div class="col-md">
				<h2>Pi Pixel Strings</h2>
			</div>
            <div class="col-md-auto ml-lg-auto">
                <div class="form-actions">
                    
                        <input type='button' class="buttons" onClick='loadPixelStringOutputs();' value='Revert'>
                        <input type='button' class="buttons" onClick='cloneSelectedString();' value='Clone String'>
						<div class="btn-group">
							<!-- <button type="button" class="buttons btn-outline-success" onclick="addPixelOutput();"><i class="fas fa-plus"></i> Add Output</button> -->
							<button type="button" class="btn btn-outline-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="fas fa-plus"></i> Add Output
							</button>
							<div class="dropdown-menu dropdown-menu-right" id="addPixelOutputTypeSelector">
								<?
								if ($settings['Platform'] == "Raspberry Pi") {
								?>
									<a class="dropdown-item" href="#" data-value="RPIWS281X" data-output-name="RPIWS281X"><i class="fas fa-plus"></i> Add RPIWS281X Output</a>
									<a class="dropdown-item" href="#" data-value="spixels" data-output-name="spixels"><i class="fas fa-plus"></i> Add spixels Output</a>
								<? } else { ?>
									<a class="dropdown-item" href="#" data-value="X11PixelStrings" data-output-name="X11 Pixel Strings"><i class="fas fa-plus"></i> Add X11 Pixel Strings Output</a>

								<?
								}
								?>
								<input type="hidden" id="pixelOutputType" value="RPIWS281X">


							</div>
						</div>
                        <input type='button' class="buttons btn-success ml-1" onClick='savePixelStringOutputs();' value='Save'>
                </div>
            </div>
        </div>


		<div id='pixelOutputs'>
		</div>
	</div>
</div>
