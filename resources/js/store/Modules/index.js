import Echo from 'laravel-echo'
import Favico from 'favico.js'

const favico = new Favico({
    animation: 'none'
});
const audio = new Audio('/notification.mp3');
const title = document.title;

const FetchApi = async function (url,options = {}) {
    let response =  await fetch(url,{
        credentials: 'same-origin',
        headers:{
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'content-type': 'application/json',
            'Accept': 'application/json'
        },
        ...options
    });
    if (response.ok){
        return response.json();
    } else {
        throw await response.json();
    }
};
const updateTitle = function (conversations) {
    let unread = Object.values(conversations).reduce((acc, conversation) => conversation.unread + acc, 0)
    if (unread === 0) {
        document.title = title
        favico.reset();
    } else {
        document.title = `(${unread}) ${title}`
        favico.badge(unread)
    }
}

const state = {
    user: null,
    openedConversations: [],
    conversations: {}
};

const getters = {
    AllUser: (state) => state.conversations,
    messages: function (state) {
        return function (id) {
            let conversation = state.conversations[id]
            if (conversation && conversation.messages){
                return conversation.messages
            } else {
                return []
            }
        }
    },
    conversation: function (state) {
        return function (id) {
            return state.conversations[id] || {}
        }
    },
    user: function (state) {
        return state.user
    }
};


const mutations = {
    setUser: function (state, userId) {
        state.user = parseInt(document.querySelector('#navbarDropdown').getAttribute('data-id'));
    },
    SetMessages: function (state, {conversations} ) {
        conversations.forEach(function (c) {
            let conversation = state.conversations[c.id] || {message: [],count: 0};
            conversation = {...conversation, ...c}
            state.conversations = {...state.conversations, ...{[c.id]: conversation}}
        })
    },
    addMessages: function (state, {messages, id, count}) {
        let conversation = state.conversations[id] || {};
        conversation.messages = messages;
        conversation.count = count;
        conversation.loading = true;
        state.conversations = { ...state.conversations, ...{[id]: conversation}}
    },
    markAsRead : function (state, id) {
        state.conversations[id].unread = 0
    },
    addMessage: function (state, {messages, id}) {
        console.log(messages)
        state.conversations[id].count++;
        state.conversations[id].messages.push(messages);
    },
    prependMessages: function (state, {messages, id}) {
        let conversation = state.conversations[id] || {};
        conversation.messages = [...messages, ...conversation.messages]
        state.conversations = { ...state.conversations, ...{[id]: conversation}}
    },
    openConversation: function (state, id) {
        state.openedConversations = [id];
    },
    incrementUnread: function (state, id) {
        let conversation = state.conversations[id];
        if (conversation){
            conversation.unread++
        } else {
            this.markAsRead(state, id)
        }
    },
    readMessage: function (state, message) {
        let conversation = state.conversations[message.from_id]
        if (conversation && conversation.messages){
            let msg = conversation.messages.find(m => m.id === message.id)
            if (msg) {
                msg.read_at = (new Date()).toISOString()
            }
        }
    }
};

const actions = {
    loadMessages: async function ({commit}) {
        let response = await FetchApi('/api/conversations');
        commit('SetMessages', { conversations: response.conversations })
    },
    changeMessages: async function (context, userId) {
        context.commit('openConversation', parseInt(userId, 10))
        if (!context.getters.conversation(userId).loading) {
            let response = await FetchApi('/api/conversations/'+ userId);
            context.commit('addMessages', { messages: response.messages ,id : userId,count: response.count})
        }
        context.getters.messages(userId).forEach((message) => {
            if (message.read_at === null && message.to_id === context.state.user) {
                context.dispatch('markAsRead', message.id)
            }
        });
        context.commit('markAsRead', userId)
        updateTitle(context.state.conversations)
    },
    sendMessages: async function (context, {content, userId}) {
        let response = await FetchApi('/api/conversations/'+userId,{
            method: 'POST',
            body: JSON.stringify({
                content: content
            })
        });
        context.commit('addMessage', {messages: response.messages, id: userId})
    },
    loadPreviousMessages: async function (context, conversationId) {
        let message = context.getters.messages(conversationId)[0]
        if (message) {
            let url = '/api/conversations/'+conversationId +'?before='+ message.created_at
            let response = await FetchApi(url);
            context.commit('prependMessages', {id: conversationId,messages: response.messages})
        }
    },
    setUser:  function (context, userId) {
        context.commit('setUser', userId);
        new Echo({
            broadcaster: 'socket.io',
            host: window.location.hostname + ":6001"
        })
            .private(`App.User.${userId}`)
            .listen("NewMessage",async function (e) {
                context.commit('addMessage', {messages: e.message, id: e.message.from_id});
                context.dispatch('loadMessages', context)
                if (!context.state.openedConversations.includes(e.message.from_id) || document.hidden){
                    context.commit('incrementUnread', e.message.from_id);
                    audio.play();
                    updateTitle(context.state.conversations)
                } else {
                    context.dispatch('markAsRead', e.message.id)
                    //this.MarkAsRead(context, e.message.id)
                }
            });
    },
    markAsRead: function (context, id) {
        FetchApi('/api/messages/'+id, { method: 'POST' })
        //context.commit('readMessage', message)
    }
};

export default {
    state,
    getters,
    actions,
    mutations
}
