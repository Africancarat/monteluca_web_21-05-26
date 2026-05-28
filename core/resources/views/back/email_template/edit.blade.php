@extends('master.back')

@section('content')

<div class="container-fluid">

	<!-- Page Heading -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h3 class=" mb-0 "><b>{{ __('Update Template') }}</b> </h3>
                <a class="btn btn-primary btn-sm" href="{{route('back.setting.email')}}"><i class="fas fa-chevron-left"></i> {{ __('Back') }}</a>
                </div>
        </div>
    </div>

	<!-- Form -->
	<div class="row">

		<div class="col-xl-12 col-lg-12 col-md-12">

			<div class="card o-hidden border-0 shadow-lg">
				<div class="card-body ">
					<!-- Nested Row within Card Body -->
					<div class="row">

                        <div class="col-lg-6">
                            <div class="gd-responsive-table">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>{{ __('BB Code') }}</th>
                                    <th>{{ __('Meaning') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ([
                                    '{user_name}' => __('Name of the customer'),
                                    '{order_cost}' => __('Order Cost'),
                                    '{site_title}' => __('Site Title'),
                                    '{transaction_number}' => __('Order Transaction Number'),
                                    '{product_list}' => __('All products with quantity and price'),
                                    '{shipping_address}' => __('Shipping address'),
                                    '{payment_method}' => __('Payment method'),
                                    '{order_status}' => __('Order status'),
                                    '{subtotal}' => __('Order subtotal'),
                                    '{discount}' => __('Discount amount'),
                                    '{tax}' => __('Tax amount (total GST)'),
                                    '{cgst}' => __('CGST amount'),
                                    '{sgst}' => __('SGST amount'),
                                    '{cgst_percent}' => __('CGST rate (e.g. 1.5)'),
                                    '{sgst_percent}' => __('SGST rate (e.g. 1.5)'),
                                    '{shipping_cost}' => __('Shipping cost'),
                                    '{grand_total}' => __('Grand total'),
                                    '{metal_type}' => __('Metal type'),
                                    '{diamond_shape}' => __('Diamond shape'),
                                    '{carat_weight}' => __('Carat weight'),
                                    '{clarity_grade}' => __('Clarity grade'),
                                    '{color_grade}' => __('Color grade'),
                                    '{estimated_delivery}' => __('Estimated delivery'),
                                    '{order_date}' => __('Order date'),
                                    '{site_url}' => __('Site URL'),
                                ] as $code => $meaning)
                                    <tr>
                                        <td>{{ $code }}</td>
                                        <td>{{ $meaning }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>


						<div class="col-lg-6">
								<form class="admin-form" action="{{ route('back.template.update',$template->id) }}"
									method="POST" enctype="multipart/form-data">

                                    @csrf

                                    @method('PUT')

									@include('alerts.alerts')

									<div class="form-group">
										<label for="subject">{{ __('Subject') }} *</label>
										<input type="text" name="subject" class="form-control" id="subject"
											placeholder="{{ __('Enter Subject') }}" value="{{ $template->subject }}" >
									</div>

									<div class="form-group">
										<label for="body">{{ __('Body') }} *</label>
										<textarea name="body" id="body" class="form-control " rows="5"
											placeholder="{{ __('Enter Email Body') }}"
											>{{ $template->body }}</textarea>
									</div>

								<div class="form-group">
										<button type="submit"
											class="btn btn-secondary ">{{ __('Submit') }}</button>
									</div>

									<div>
								</form>
                        </div>

					</div>
				</div>
			</div>

		</div>

	</div>

</div>

@endsection
