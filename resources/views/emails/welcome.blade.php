<!DOCTYPE html>
<html lang="en">
<body>
	<div style="font-family: arial,helvetica neue,helvetica,sans-serif; font-size: 14px; background: #fff; line-height: 1.5; border: 2px solid #eceded; background: #fff; width: 100%; max-width: 600px; margin: 0 auto;">

		<table style="border-spacing: 10px; width: 100%; background-color: #0f3844">
			<tbody>
				<tr>
					<td align="center">
						<span style="color: #fff; font-size: 16px;"> PayDonk
						</span> 
					</td>
				</tr>
			</tbody>
		</table>

		<div>
			<img src="{{ $message->embed(asset('images/emails/welcome.jpg')) }}" width="100%" alt="banner">
		</div>

		<div style="text-decoration: none; padding: 0 20px">
			<div style="color: #0f3844">
				<p><b>Hai, {{ $user->first_name }} @if ($user->last_name) {{ $user->last_name }}@endif!</b></p>
				<p style="margin-top:10px">Welcome to PayDonk.</p>
			</div>
		</div>

		<div align="center" style="text-align: center; margin: 20px; padding: 0">
			<a href="http://gethype.co.id/" target="_blank" style="color: #fff; text-decoration: none; display: inline-block; text-align: center; padding: 12px 16px; border-radius: 0; font-size: 15px; line-height: 1.3; font-weight: lighter; min-width: 20%; max-width: 50%; overflow: hidden; word-wrap: break-word; background-color: #d33e40">Visit PayDonk</a> 
		</div>

		<table style="margin-top: 30px; border-top: 1px solid #d6d6d6; background-color: #fff; width: 100%; border-spacing: 10px;">
			<tbody>
				<tr>
					<td style="margin: 0; padding: 0; width: 45%; text-align: center">
						<div style="margin-bottom: 10px;margin-top: 20px;">
							<img src="{{ $message->embed(asset('images/emails/logo-footer-email.png')) }}">
						</div>
						<div style="color:#606060; font-size:12px;line-height: 30px;">
							<a href="http://gethype.co.id/" target="_blank" style="text-decoration: none; color:#0f3844;">Website</a> &nbsp;|&nbsp; <a href="http://gethype.co.id/contact-us" target="_blank" style="text-decoration: none; color:#0f3844;">Contact</a> &nbsp;|&nbsp; <a href="http://gethype.co.id/services" target="_blank" style="text-decoration: none; color:#0f3844;">Service</a><br>
							<a href="" target="_blank" style="text-decoration:none">
								<img src="{{ $message->embed(asset('images/emails/facebook.png')) }}" style="margin-top: 5px; height: 15px;margin-right: 15px;"> 
							</a>
							<a href="" target="_blank" style="text-decoration:none">
								<img src="{{ $message->embed(asset('images/emails/twitter.png')) }}" style="margin-top: 5px; height: 15px;margin-right: 10px;"> 
							</a>
							<a href="https://www.instagram.com/gethype.id/" target="_blank" style="text-decoration:none">
								<img src="{{ $message->embed(asset('images/emails/instagram.png')) }}" style="margin-top: 5px; height: 15px;margin-right: 10px;"> 
							</a>
							<a href="" target="_blank" style="text-decoration:none">
								<img src="{{ $message->embed(asset('images/emails/linkedin.png')) }}" style="margin-top:5px; height: 15px;"> 
							</a>
							<br>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<table style="border-spacing: 10px; width: 100%; background-color: #0f3844">
			<tbody>
				<tr>
					<td align="center">
						<span style="color: #fff; font-size: 12px;"> 2017 | PayDonk
						</span> 
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</body>
</html>
