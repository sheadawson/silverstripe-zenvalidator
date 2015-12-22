// ParsleyConfig definition if not already set
window.ParsleyConfig = window.ParsleyConfig || {};
window.ParsleyConfig.i18n = window.ParsleyConfig.i18n || {};

// Define then the messages
window.ParsleyConfig.i18n.sk = jQuery.extend(window.ParsleyConfig.i18n.sk || {}, {
  defaultMessage: "Zadaná hodnota je neplatná.",
  type: {
    email:        "Prosím zadajte platnú e-mailovú adresu."
  }
});

// If file is loaded after Parsley main file, auto-load locale
if ('undefined' !== typeof window.ParsleyValidator)
  window.ParsleyValidator.addCatalog('sk', window.ParsleyConfig.i18n.sk, true);
