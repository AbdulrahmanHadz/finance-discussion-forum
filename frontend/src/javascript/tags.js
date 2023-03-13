import { defineStore } from "pinia";
import { fetchData } from './api.js'


export const tagsData = () => {
    const innerStore = defineStore({
        id: "tags",
        state: () => ({
            tags: []
        }),

        actions: {
            fetchTags() {
                fetchData('tags')
                    .then(res => {
                        this.tags = res.data.data
                        console.log(this.tags)
                    })
                    .catch(err => {
                        console.log(err)
                    })
            },
        },
    });
    const s = innerStore();
    if (s.tags.length == 0) {
        s.fetchTags();
    }
    return s;
};

