<template>
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <template v-for="conversation in AllUser">
                    <router-link :to="{ name: 'conversation', params: { id: conversation.id }}" class="list-group-item d-flex justify-content-between align-center">
                        {{ conversation.name }}
                        <span class="badge badge-pill badge-primary" v-if="conversation.unread">
                         {{ conversation.unread }}
                    </span>
                    </router-link>
                </template>
            </div>
        </div>
        <div class="col-md-9">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex';

    export default {
        props: {
            user: Number
        },
        methods: {
            ...mapActions(['loadMessages'])
        },
        computed:{
            ...mapGetters(['AllUser'])
        },
        created () {
            this.loadMessages()
        },
        mounted () {
            let valeur = parseInt( document.querySelector('#navbarDropdown').getAttribute('data-id'));
            this.$store.dispatch('setUser', valeur)
        }
    }
</script>
