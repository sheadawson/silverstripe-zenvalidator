(function($) {
	$.entwine('ss.zenvalidator', function($) {

		$('form.parsley').entwine({
			onmatch: function() {
					// Fix validation for optionset based class (attributes not set on child options)
					$(this).find('.optionset').each(function() {
						var attrs = $.makeArray(this.attributes);
						var parsley = {};
						for(var i=0; i < attrs.length; i++) {
							var att = attrs[i];
							if(att.name.indexOf('parsley-') !== -1) {
								parsley[att.name] = att.value;
							}
						}
						$(this).find('input').attr(parsley);
					});
					
        			$(this).parsley({
            				excluded: 'input[type=button], input[type=submit], input[type=reset], input[type=hidden], :hidden, .ignore-validation',
							errorsContainer: function (el) {
								return el.$element.closest(".field");
							}
        			});
			}
		});
		
		$.listen('parsley:field:error', function(fieldInstance) {
			if(!fieldInstance._xhr) {
				return;
			}
			if(fieldInstance._xhr.status < 400 || fieldInstance._xhr.status > 499) {
				return;
			}
			fieldInstance.options['remoteMessage'] = fieldInstance._xhr.responseText;
		});

		$('.field').entwine({

			getFormField: function() {
				return this.find('[name='+this.getFieldName()+'], [name="'+this.getFieldName()+'[]"]');
			},

			getFieldName: function() {
				return this.attr('id');
			},

			getFieldValue: function() {
				return this.getFormField().val();
			},

			evaluateEqualTo: function(val) {
				return this.getFieldValue() === val;
			},

			evaluateNotEqualTo: function(val) {
				return this.getFieldValue() !== val;
			},

			evaluateLessThan: function(val) {
				num = parseFloat(val);
				return this.getFieldValue() < num;
			},

			evaluateGreaterThan: function(val) {
				num = parseFloat(val);
				return parseFloat(this.getFieldValue()) > num;
			},

			evaluateContains: function(val) {
				return this.getFieldValue().match(val) !== null;
			},

			evaluateEmpty: function() {
				return $.trim(this.getFieldValue()).length === 0;
			},

			evaluateNotEmpty: function() {
				return !this.evaluateEmpty();
			},

			evaluateChecked: function() {
				return this.getFormField().is(":checked");
			}


		});


		$('.field.validation-logic').entwine({
			onmatch: function () {
				masters = this.getMasters();
				for(m in masters) {
					this.closest('form').find('#'+masters[m]).addClass("validation-logic-master");				
				}
			},

			getLogic: function() {
				return $.trim(this.getFormField().data('validation-logic-eval'));
			},

			parseLogic: function() {
				js = this.getLogic();
				result = eval(js);			
				return result;
			},

			getMasters: function() {
				var field = this.getFormField();
				if(!field.length) {
					return new Array();
				}
				return field.data('validation-logic-masters').split(",");
			}

		});


		$('.field.optionset').entwine({

			getFormField: function() {
				f = this._super().filter(":checked");			
				return f;
			}

		});


		$('.field.optionset.checkboxset').entwine({

			evaluateHasCheckedOption: function(val) {
				this.find(':checkbox').filter(':checked').each(function() {
					return $(this).val() === val || $(this).getLabel() === val;
				})
			},

			evaluateHasCheckedAtLeast: function(num) {
				return this.find(':checked').length >= num;
			},

			evaluateHasCheckedLessThan: function(num) {
				return this.find(':checked').length <= num;	
			}
		});

		$('.field input[type=checkbox]').entwine({
			getLabel: function() {
				return this.closest('form').find('label[for='+this.attr('id')+']');
			}
		});

		$('.field.validation-logic.validation-logic-validate').entwine({
			testLogic: function() {
				this.getFormField().toggleClass('ignore-validation', this.parseLogic());
			}
		});


		$('.field.validation-logic.validation-logic-exclude').entwine({
			testLogic: function() {
				this.getFormField().toggleClass('ignore-validation', !this.parseLogic());
			}
		});

		$('.field.validation-logic-master :text, .field.validation-logic-master select').entwine({
			onmatch: function() {
				this.closest(".field").notify();
			},

			onchange: function() {
				this.closest(".field").notify();
			}
		});

		$('.field.validation-logic-master :checkbox, .field.validation-logic-master :radio').entwine({
			onmatch: function() {			
				this.closest(".field").notify();
			},

			onclick: function() {			
				this.closest(".field").notify();
			}
		});

		$('.field.validation-logic-master').entwine({
			Listeners: null,

			notify: function() {
				$.each(this.getListeners(), function() {				
					$(this).testLogic();
				});
			},

			getListeners: function() {
				if(l = this._super()) {
					return l;
				}
				var self = this;
				var listeners = [];
				this.closest("form").find('.validation-logic').each(function() {
					masters = $(this).getMasters();
					for(m in masters) {
						if(masters[m] == self.attr('id')) {
							listeners.push($(this));
							break;
						}
					}
				});
				this.setListeners(listeners);
				return this.getListeners();
			}
		});
	});	

})(jQuery);
