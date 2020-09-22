module.exports = {
  extends: 'stylelint-config-standard',
  plugins: [
    "stylelint-scss"
  ],
  rules: {
    "indentation": [2],
    "number-leading-zero": null,
    // Replace CSS @ with SASS ones
    "at-rule-no-unknown": null,
    "scss/at-rule-no-unknown": true,
    // not compatible with SASS apparently
    "no-descending-specificity": null
  },
}
