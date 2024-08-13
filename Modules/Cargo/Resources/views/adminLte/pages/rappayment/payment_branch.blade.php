<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
    .txt-align-left {
        text-align: left;
    }

    .txt-align-right {
        text-align: right;
    }

    .vertical-align-middle {
        vertical-align: middle;
    }

    span.state {
        display: inline;
    }

    .page-break {
        page-break-after: always;
    }
	.container {
			text-align: center;
			width: 100%;
			max-width: 794px; /* A4 paper width in pixels (assuming 72 dpi) */
			margin: 0 auto;
		}

		.logo-container {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 20px;
		}

		.logo {
			width: 100px;
			height: 100px;
		}

		.left-logo {
			margin-right: 10px;
		}

		.right-logo {
			margin-left: 10px;
		}

		.title {
			margin-bottom: 10px;
			text-align: center;
		}
		.title1 {
			margin-bottom: 20px;
			text-align: center;
		}

</style>

<div>

    <div class="page " style="padding:0px;">
        <div class="subpage">
            <table width="750px"
                style="font-weight:500px;font-size:19px;font-family:Arial, Helvetica, sans-serif; border-top: 1px solid #000000; border-bottom: 1px solid #000000;">
                <tr>
                    <td>
                        @if (get_setting('system_logo_white') != null)
                            <img src="{{ uploaded_asset(get_setting('system_logo_white')) }}" height="130vh">
                        @endif

                    </td>
                    <td style="font-size:40px;">
                        COLIRELI SPA
                    </td>
					<td class="txt-align-right" style="right:0px">
						@if (get_setting('system_logo_white') != null)
                            <img src="{{ uploaded_asset(get_setting('system_logo_white')) }}" height="130vh">
                        @endif
                    </td>
                    
                </tr>
            </table>

            






        </div>
		<div class="container">
			<p>{{ now() }}</p>
			<h1 class="title1">{{ __('Payment Report') }}</h1>
			
	
			<h2 class="title">{{ __('Delivered') }}:</h2>
			<p>{{ $data['delivered'] .' '. __('shipments') }}</p>
			
			<h2 class="title">{{ __('Returned') }}:</h2>
			<p>{{ $data['returned'] .' '. __('shipments') }}</p>

			<h2 class="title">{{ __('Collected') }}:</h2>
			<p>{{ format_price($data['collected']) }}</p>
			
			<h2 class="title">{{ __('Charged') }}:</h2>
			<p>{{ format_price($data['charged']) }}</p>
			
			<h2 class="title">{{ __('Net') }}:</h2>
			<strong>{{ format_price($data['net']) }}</strong>
		</div>
    </div>
	<br />
	<br /><br />
	<br />
	<p style="margin-top: 25px">Signature</p>
    
    <br />

</div>
