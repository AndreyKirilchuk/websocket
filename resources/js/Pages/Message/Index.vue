<script setup>
    import {ref} from "vue";


    const props = defineProps({
        'messages': Array
    })

    const messages = ref([...props.messages].reverse());
    const body = ref('')
    const timeBroker = ref(0);
    const errorAnimate = ref(false);
    import Swal from 'sweetalert2';

    const interval = setInterval(() => {
        if (timeBroker.value > 0) {
            timeBroker.value--;
        }
    }, 1000);

    const applyBody = () => {
        if(body.value.length > 0){
            errorAnimate.value = false;
        }
    }

    const store = () => {
        if(body.value !== ''){
            if(timeBroker.value > 0){
                Swal.fire({
                    text: `You can send a message in ${timeBroker.value} seconds`,
                    timer: timeBroker.value * 1000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    position: 'top'
                });
                return
            }

            try {
                axios.post('/messages', {body: body.value})
                    .then(res => {
                        messages.value.unshift(res.data)
                        body.value = ''
                        timeBroker.value = 5
                    })
            }catch(err){
                console.log(err)
            }
        }else{
            errorAnimate.value = false

            setTimeout(()=>{
                errorAnimate.value = true;
            },1)
        }
    }

    window.Echo.channel('store_message').listen('.store_message', res => {
        messages.value.unshift(res.message)
    })
</script>

<template>
    <div class="text-blue-500 bg-white text-xl font-bold w-full p-4 shadow">
        <h3>Anonimys messages</h3>
    </div>

    <div v-if="messages.length > 0" class="p-4 pb-20" v-auto-animate>
        <div v-for="message in messages" :key="message.id" class="mb-4 p-4 break-words bg-white shadow rounded-lg">
            <p class="text-blue-500 font-semibold hidden">{{ message.id }}</p>
            <p class="text-gray-600">{{ message.body }}</p>
            <p class="text-gray-400 text-sm">{{ message.time }}</p>
        </div>
    </div>

    <div class="fixed bottom-0 w-full flex justify-center md:justify-start p-4 bg-white shadow-top">
        <input
            type="text"
            placeholder="Your message"
            class="w-full  px-4 py-2 border border-gray-300 rounded-lg focus:outline-none "
            v-model="body"
            @input="applyBody"
            :class="{'error': errorAnimate}"
        >

        <button class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-lg hidden sm:block" :class="{'error': errorAnimate}" @click="store">
            Send
        </button>

        <button class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-lg block sm:hidden" @click="store" :class="{'error': errorAnimate}">
            S
        </button>
    </div>
</template>

<style scoped>

button{
    border: 1px solid rgb(59 130 246);
    transition: 0.3s;
}

input:focus{
    border: 1px solid rgb(59 130 246);
}

.error {
    border: 1px solid #cc1616 !important;
    animation: shake 0.5s;
}

@keyframes shake {
    0% {
        transform: translateY(2px);
    }
    25% {
        transform: translateY(-4px);
    }
    50% {
        transform: translateY(3px);
    }
    75% {
        transform: translateY(-2px);
    }
    100% {
        transform: translateY(0);
    }
}

.shadow-top {
    box-shadow: 0 -10px 15px -3px rgba(0, 0, 0, 0.1), 0 -4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>
