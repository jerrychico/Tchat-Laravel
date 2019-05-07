<template>
    <div class="card">
        <div class="card-header">{{name}}</div>
        <div class="card-body messagerie__body">
            <Message :message="message" :key="message.id" v-for="message in messages" :user="user"/>
            <form action="" method="post" class="messagerie__form">
                <div class="form-group">
                    <textarea name="content" v-model="content" placeholder="Ecrivez votre message"
                              :class="{'form-control': true, 'is-invalid': errors.errors}"
                              @keypress.enter="sendMessages">
                    </textarea>
                    <div class="invalid-feedback">{{errors.errors }}</div>
                </div>
            </form>
            <div class="messagerie__loading" v-if="loading">
                <div class="loader"></div>
            </div>
        </div>
    </div>
</template>

<script>
    import Message from './Message'
    import {mapGetters} from 'vuex'

    export default {
        components: {Message},
        data () {
            return {
                content: ' ',
                errors: {},
                loading: false
            }
        },
        computed: {
            ...mapGetters(['user']),
            messages: function () {
                return this.$store.getters.messages(this.$route.params.id)
            },
            LastMessage: function () {
                return this.messages[this.messages.length - 1]
            },
            count: function () {
                return this.$store.getters.conversation(this.$route.params.id).count
            },
            name: function () {
                return this.$store.getters.conversation(this.$route.params.id).name
            }
        },
        mounted () {
            this.loadMessages(),
            this.$messages = this.$el.querySelector('.messagerie__body');
            document.addEventListener('onvisibilitychange', this.onVisible)
        },
        watch: {
            '$route.params.id': function () {
                this.loadMessages()
            },
            LastMessage: function () {
                this.scrollBot();
                this.loadMessages()
            }
        },
        destroyed () {
            document.removeEventListener('onvisibilitychange', this.onVisible)
        },
        methods: {
            onVisible: async function () {
              if (document.hidden === false){
                  await this.$store.dispatch('changeMessages', this.$route.params.id);
              }
            },
            loadMessages: async function () {
                await this.$store.dispatch('changeMessages', this.$route.params.id);
                if (this.messages.length < this.count){
                    this.$messages.addEventListener('scroll', this.onScroll)
                }
            },
            scrollBot () {
                this.$nextTick(() => {
                    this.$messages.scrollTop = this.$messages.scrollHeight
                })
            },
            async onScroll () {
                if (this.$messages.scrollTop === 0) {
                    this.loading = true
                    this.$messages.removeEventListener('scroll', this.onScroll)
                    let PreventHeight = this.$messages.scrollHeight;
                    await this.$store.dispatch('loadPreviousMessages', this.$route.params.id)
                    this.$nextTick(()=>{
                        this.$messages.scrollTop = this.$messages.scrollHeight - PreventHeight
                    })
                    if (this.messages.length < this.count){
                        this.$messages.addEventListener('scroll', this.onScroll)
                    }
                    this.loading = false
                }
            },
            async sendMessages (e) {
                if (e.shiftKey === false) {
                    try {
                        this.loading = true;
                        this.errors = {};
                        e.preventDefault();
                        await this.$store.dispatch('sendMessages', {
                            content: this.content,
                            userId: this.$route.params.id
                        });
                        this.content = '';
                    }catch (e) {
                        if (e.errors){
                            this.errors = e
                        } else {
                            console.error(e)
                        }
                    }
                    this.loading = false;
                }
            }
        }
    }
</script>

<style scoped>

</style>
