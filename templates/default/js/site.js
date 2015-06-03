var Site = {
	language: 'russian',
	language_file: '',
	url: '',
	lang: {},
	debug: false,
	
	
	init: function() {
		// override jquery validate plugin defaults
		$.validator.setDefaults({
			highlight: function(element) {
				if (element.type === "radio") {
					this.findByName(element.name).addClass(errorClass).removeClass(validClass);
				} else {
					$(element).closest('.form-group').removeClass('has-success has-feedback').addClass('has-error has-feedback');
					$(element).closest('.form-group').find('i.fa').remove();
					$(element).closest('.form-group').append('<i class="fa fa-exclamation fa-lg form-control-feedback"></i>');
				}
			},
			unhighlight: function(element) {
				if (element.type === "radio") {
					this.findByName(element.name).removeClass(errorClass).addClass(validClass);
				} else {
					$(element).closest('.form-group').removeClass('has-error has-feedback').addClass('has-success has-feedback');
					$(element).closest('.form-group').find('i.fa').remove();
					$(element).closest('.form-group').append('<i class="fa fa-check fa-lg form-control-feedback"></i>');
				}
			},
			errorElement: 'span',
			errorClass: 'help-block',
			errorPlacement: function(error, element) {
				if(element.parent('.input-group').length) {
					error.insertAfter(element.parent());
				} else {
					error.insertAfter(element);
				}
			}
		});
		
		$('form[data-ajax-form]').validate({
			submitHandler: function(form) {
				submitDataType = (Site.debug == 2) ? 'html' : 'json';
				
				$(form).ajaxSubmit({
					dataType: submitDataType,
					success: function(data) {
						if (Site.debug) console.log(data);
						if (Site.debug == 2) return;
						
						if (!data.status) {
							Site.alerts.error(data.message);
						}
						else {
							disableForm = (typeof (data.disableForm) != 'undefined') ? data.disableForm : true;
							/**
							* Если есть редирект с задержкой - показываем сообщение и делаем задержку
							* Если есть редирект без задержки - далаем сразу, без сообщения
							* Если нет редиректа, показываем сообщение
							
							* скрываем форму, если нет параметра disableForm
							*/
							if (disableForm) $(form).fadeOut(700);
							
							if (typeof (data.redirect) != 'undefined') {
								if (typeof (data.redirectDelay) != 'undefined') {
									Site.alerts.error(data.message);
									
									setTimeout(function() {
										document.location.href = data.redirect;
									}, data.redirectDelay);
								}
								else {
									document.location.href = data.redirect;
								}
							}
							else {
								Site.alerts.error(data.message);
							}
						}
					},
					
					beforeSubmit: function() {
						$('.alert').remove();
					},
					
					data: {ajax:true, answerType: 'json'},
				});
			},
			
			ignore: ".ignore, :hidden",
			focusInvalid: true,
		});
		
		$('body').on('click', '.alert', function() {
			$(this).fadeOut(500, function() {
				$(this).remove();
			});
		});
		
		//$('input, select, textarea').styler();
		
		/**$('form[data-ajax-form]').validate({
			submitHandler: function(form) {
				$(form).ajaxSubmit();
			},
		});*/
	},
	
	alert: function(type, message) {
		$('.container').prepend('<div class="alert alert-'+type+'">'+message+'</div>');
	},
	
	alerts: {
		error: function (message) {
			Site.alert('danger', message);
		},
		
		success: function (message) {
			Site.alert('success', message);
		},
		
		info: function (message) {
			Site.alert('info', message);
		},
	},
	
	setLanguage: function (language, file) {
		//alert(this.url+"/"+file);
		$.getScript(this.url+"/"+file, function(){
			this.lang = Lang;
			Lang = false;
		});
	},
	
	setUrl: function (url) {
		this.url = url;
	}
};

$(document).ready(function() {
	Site.init();
});