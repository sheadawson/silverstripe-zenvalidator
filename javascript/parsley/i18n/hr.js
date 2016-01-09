// ParsleyConfig definition if not already set
window.ParsleyConfig = window.ParsleyConfig || {};
window.ParsleyConfig.i18n = window.ParsleyConfig.i18n || {};

// Define then the messages
window.ParsleyConfig.i18n.hr = $.extend(window.ParsleyConfig.i18n.hr || {}, {
  defaultMessage: "Vrijednost je nevažeća.",
  type: {
    email:        "Vrijednost treba biti valjan email.",
    url:          "Vrijednost treba biti valjan url.",
    number:       "Vrijednost treba biti valjan broj.",
    integer:      "Vrijednost treba biti valjan broj.",
    digits:       "Vrijednost treba biti brojčana.",
    alphanum:     "Vrijednost treba biti alfanumerička."
  },
  notblank:       "Vrijednost je može biti prazna.",
  required:       "Polje je obavezno.",
  pattern:        "Vrijednost je nevažeća.",
  min:            "Vrijednost treba biti veća ili jednaka od %s.",
  max:            "Vrijednost treba biti manja ili jednaka od %s.",
  range:          "Vrijednost treba biti između %s i %s.",
  minlength:      "Vrijednost je prekratka. Treba imati %s znakova ili više.",
  maxlength:      "Vrijednost je preduga. Treba imati %s znakova ili manje.",
  length:         "Vrijednost je nevažeća. Treba imati između %s i %s znakova.",
  mincheck:       "Izaberite najmanje %s odgovor.",
  maxcheck:       "Izaberite %s ili manje odgovora.",
  check:          "Izaberite između %s i %s.",
  equalto:        "Vrijednost treba biti ista."
});

// If file is loaded after Parsley main file, auto-load locale
if ('undefined' !== typeof window.ParsleyValidator)
  window.ParsleyValidator.addCatalog('hr', window.ParsleyConfig.i18n.hr, true);
