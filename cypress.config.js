const { defineConfig } = require("cypress");
require('dotenv').config();

module.exports = defineConfig({
  viewportWidth: 1920,
  viewportHeight: 1080,
  e2e: {
    supportFile : false,
    baseUrl: process.env.CYPRESS_BASE_URL,
    chromeWebSecurity: false,
    setupNodeEvents(on, config) {
    },
    specPattern: [
      'cypress/regmel/e2e/**/*.cy.js',
    ],
  },
});
