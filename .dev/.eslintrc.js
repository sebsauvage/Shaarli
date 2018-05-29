module.exports = {
  "extends": "airbnb-base",
  "env": {
    "browser": true,
  },
  "rules": {
    "no-param-reassign": 0, // manipulate DOM style properties
    "no-restricted-globals": 0, // currently Shaarli uses alert/confirm, could be be improved later
    "no-alert": 0, // currently Shaarli uses alert/confirm, could be be improved later
    "no-cond-assign": [2, "except-parens"], // assignment in while loops is readable and avoid assignment duplication
  }
};
