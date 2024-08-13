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
		text-align: left;
		width: 100%;
		max-width: 794px;
		/* A4 paper width in pixels (assuming 72 dpi) */
		margin: 0 auto;
	}

	.cont {
		text-align: center;
		width: 100%;
		max-width: 794px;
		/* A4 paper width in pixels (assuming 72 dpi) */
		margin: 0 auto;
	}

	.logo-container {
		display: flex;
		align-items: left;
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
		text-align: left;
	}

	.tit {
		margin-bottom: 10px;
		text-align: center;
		text-decoration: underline;
	}

	.titlex {
		text-decoration: underline;
	}

	.title1 {
		margin-bottom: 20px;
		text-align: left;
	}

	p {
		font-size: 20px;
	}

	ul {
		margin-left: 35px;
		margin-top: -10px;
	}

	li {
		margin: -10px;
		padding: -10px;
	}

	.left {
		margin-left: 40px;
	}
</style>

<div>

	<div class="page " style="padding:0px;">
		<div class="subpage">
			<table width="750px"
				style="font-weight:500px;font-size:19px;font-family:Arial, Helvetica, sans-serif; border-bottom: 1px solid #000000;">
				<tr>
					<td class="left" style="font-size:40px;">
						<h5 class="left"><strong>Société Par Actions ColiReli</strong></h5>
						<p>Société par actions au capital de 10 000 000 DZD</p>
						<p>160 logts participatifs lot 5 ,6 n°9 Essareg</p>
						<p>El Eulma-Sétif</p>
					</td>
					<td class="txt-align-right" style="right:0px">
						@if (get_setting('system_logo_white') != null)
						<img src="{{ asset('black2.png') }}" height="100vh">
						@endif
					</td>

				</tr>
			</table>








		</div>
		<div class="cont">
			<h1 class="tit"><strong>Accuser De Réception Du Paiement</strong></h1>
		</div>
		<div class="container left">

			<h2 class="titlex"><strong>{{ __('Client') }}:</strong></h2>

			<ul>
				<li>
					<h3>Store :&ensp; {{ $data['store'] }}</h3>
				</li>
				<li>
					<h3>Code client :&ensp; {{ $data['client_code'] }}</h3>
				</li>
				<li>
					<h3>Téléphone :&ensp; {{ $data['phone'] }}</h3>
				</li>
			</ul>

			<h4 style="margin-left: 20px; margin-top: 20px;">Nous vous confirmons votre règlement du paiement N° {{ $data['code'] }}, à la date du {{ now()->format('d/m/Y') }}</h4>
			<h4 style="margin-left: 20px; margin-top: 20px;">Nous vous bien reçu votre règlement d’un montant total en
				chiffre __ <strong>{{ format_price($data['net']) }}</strong> __,
				et en Lattre <strong style="text-transform: uppercase;">{{ Rmunate\Utilities\SpellNumber::value($data['net'])->locale('fr')->toLetters() }}</strong></h4>

			<h2 class="titlex"><strong>Résumé de rapport :</strong></h2>

			<ul>
				<li>
					<h3>Total Colis : &ensp;  {{ (int)$data['delivered'] + (int)$data['returned'] }}</h3>
				</li>
				<li>
					<h3>Colis Livrée : &ensp;  {{ $data['delivered'] }}</h3>
				</li>
				<li>
					<h3>Colis Returner : &ensp;  {{ $data['returned'] }}</h3>
				</li>
				<li>
					<h3>Total montant collecté : &ensp; {{ format_price($data['collected']) }}</h3>
				</li>
				<li>
					<h3>Total des frais de livraison : &ensp; {{  format_price($data['charged'])  }}</h3>
				</li>
				<li>
					<h3>Net à payer : &ensp;{{ format_price($data['net']) }}</h3>
				</li>
			</ul>

			<h4>Nous vous remercions et vous prions d’agrées, nos salutation distinguées.</h4>

		</div>
	</div>
	<br />
	<div class="subpage">
		<table width="750px"
			style="font-weight:500px;font-size:19px;font-family:Arial, Helvetica, sans-serif;">
			<tr>
				<td class="left" style="font-size:25px;">
					<p>Client (Nom, Signature)</p>
				</td>
				<td class="txt-align-right" style="right:0px">
					<p>Société De Livraison
						Bureau : {{ $data['branch'] }}</p>
				</td>

			</tr>
		</table>

	</div>
	<br><br><br><br><br><br><br><br><br>
	<div class="subpage">
		<table width="750px"
			style="font-weight:500px;font-size:19px;font-family:Arial, Helvetica, sans-serif; border-top: 1px solid #000000; border-bottom: 1px solid #000000; padding: -20 px;">
			<tr style="margin-top: -10px;margin-buttom: -10px;">
				<td>
					@if (get_setting('system_logo_white') != null)
						<img src="{{ uploaded_asset(get_setting('system_logo_white')) }}" height="130vh">
					@endif

				</td>
				<td style="font-size:20px;">
					<h6>RC: 21B1162486</h6>
					<h6>NIF: 002119116248613</h6>
					<h6>160 logts participatifs lot 5 ,6 n°9 Essareg, EL-Eulma, SETIF</h6>
				</td>
				<td style="font-size:20px;">
					<h6>NIS: 002119200064533</h6>
					<h6>ART: 19201473921</h6>
					
				</td>
				
			</tr>
		</table>
	</div>


</div>

