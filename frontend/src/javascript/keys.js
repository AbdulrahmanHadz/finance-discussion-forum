import { ref } from 'vue';
export const headerKey = ref(0);

export const forceRerenderHeaderKey = () => {
    headerKey.value += 1;
};