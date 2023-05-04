import Vue from 'vue'
import index from '../vue/index.vue';
import Vuetify from 'vuetify'
import Vuex from 'vuex'

Vue.use(Vuetify)
Vue.use(Vuex)

var index = {
    template: '#app',
    props: {
    },
    computed: {
      }
    }


new Vue({
    el: '#app',
    data: data,
    components: {
        index,
    }, 
    data: function(){
        return{

        }
    },
})