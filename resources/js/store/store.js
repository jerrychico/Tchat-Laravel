import Vuex from 'vuex'
import Vue from 'vue'
import index from './Modules/index'


Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        index: index
    }
})
