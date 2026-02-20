import Vue from 'vue';
import ChartsPage from "../../vue/dashboard-graphics/Charts.vue";

window.app = new Vue({
  el: '#vue-charts',
  components: { ChartsPage },
  template: '<ChartsPage/>'
});

