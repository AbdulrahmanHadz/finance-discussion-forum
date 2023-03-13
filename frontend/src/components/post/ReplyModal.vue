<script>
import CloseIcon from '../icons/CloseIcon.vue'
import { loggedInId } from '../../javascript/loggedin.js'
import { fetchData, postData, updateData } from '../../javascript/api';

export default {
    data() {
        return {
            userId: null,
            form: {
                content: null,
                postId: null,
                userId: null
            },
            respMessage: null
        }
    },
    created() {
        this.form.userId = this.userId = loggedInId()
        this.form.postId = this.postId

        if (this.content != null) {
            this.form.content = this.content
        }
    },
    methods: {
        close() {
            this.$emit('close');
        },
        send(event) {
            if (this.form.content == null || this.form.content.length == 0) {
                this.close()
                return
            }

            if (!this.content || this.content.length == 0) {
                this.addNewComment()
                return
            } else {
                this.updateComment()
                return
            }
        },
        updateInput(event) {
            this.form.content = event.target.value
        },
        addNewComment() {
            if (this.form.content == null || this.form.content.length == 0) {
                return
            }

            postData('comments', this.form)
                .then(res => {
                    let commentId = res.data.data.id
                    this.fetchNewComment(commentId)
                })
                .catch(err => {
                    console.log(err)
                    const errorResponse = err.response
                    if (errorResponse) {
                        const errorData = errorResponse.data
                        const messageInError = "message" in errorData
                        if (messageInError) {
                            this.respMessage = errorData.message
                        }
                    } else {
                        this.respMessage = 'Something went wrong'
                    }
                })
        },
        fetchNewComment(commentId) {
            fetchData(`comments/${commentId}`).then(res => {
                let commentData = res.data.data
                this.$emit('new', commentData)
                this.close()
            })
        },
        updateComment() {
            updateData('comments', this.form.postId, this.form)
                .then(res => {
                    this.$emit('update',
                        {
                            'id': this.postId,
                            'editedAt': new Date().toISOString(),
                            'content': this.form.content
                        }
                    )
                    this.close()
                })
                .catch(err => {
                    console.log(err)
                    const errorResponse = err.response
                    if (errorResponse) {
                        const errorData = errorResponse.data
                        const messageInError = "message" in errorData
                        if (messageInError) {
                            this.respMessage = errorData.message
                        }
                    } else {
                        this.respMessage = 'Something went wrong'
                    }
                })
        }
    },
    components: {
        CloseIcon
    },
    props: {
        postId: {
            type: String, required: true
        },
        content: {
            type: String, default: null
        }
    }
};
</script>

<template>
    <transition name="reply-modal-fade">
        <div class="reply-modal-backdrop" id="modal">
            <div class="reply-modal-base" role="form" id="send-reply" aria-label="Add a reply">
                <div class="reply-modal-header" id="modalTitle">
                    <label class="reply-modal-header" v-if="userId != null">Comment</label>
                    <p v-if="userId == null" class="error">You need to login to be able to post a comment.</p>
                    <CloseIcon aria-label="Close modal" class="btn-close" @click="close" />
                </div>
                <div id="form-div" v-if="userId != null">
                    <p class="error">{{ respMessage }}</p>
                    <div class="form">
                        <textarea :value="form.content" class="modal-reply" @input="updateInput" />
                        <v-btn elevation="2" class="btn-green" @click="send">Comment</v-btn>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style>
.form {
    display: flex;
    flex-direction: column;
}

.reply-modal-header {
    font-size: 1.25rem;
    line-height: 1.75rem;
    font-weight: 700;
}

.modal-reply {
    border: solid 2px black;
    border-radius: 0.375rem;

    padding: 0.5rem;

    min-height: 14vh;
    height: 30vh;

    margin-top: 2%;
    margin-bottom: 5%;
}

.reply-modal-backdrop {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: rgba(0, 0, 0, 0.3);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    min-height: 30%;
}

.reply-modal-base {
    background: #FFFFFF;
    box-shadow: 2px 2px 20px 1px;
    overflow-x: auto;
    display: flex;
    flex-direction: column;

    padding-top: 20px;
    padding-bottom: 20px;
    padding-right: 20px;
    padding-left: 20px;
    width: 50%;
    min-height: 30%;
    max-height: 90%;
    border-radius: 0.375rem;
}

.reply-modal-header,
.modal-footer {
    display: flex;
}

.reply-modal-header {
    color: var(--header);
    justify-content: space-between;
    display: flex;
}

.modal-footer {
    border-top: 1px solid #eeeeee;
    flex-direction: column;
}

.modal-body {
    position: relative;
    padding: 20px 10px;
}

.btn-close {
    cursor: pointer;
}

.btn-green {
    color: white;
    background: #9173b7;
    border: 1px solid #9173b7;
    border-radius: 2px;
}

.reply-modal-fade-enter,
.reply-modal-fade-leave-to {
    opacity: 0;
}

.reply-modal-fade-enter-active,
.reply-modal-fade-leave-active {
    transition: opacity .7s ease;
}
</style>