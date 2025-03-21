import { ref } from 'vue';

const flashMessageRef = ref(null);

export const useFlashMessage = () => {
    return {
        flashMessageRef,
    };
};