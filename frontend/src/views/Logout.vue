<script>
import { clearLocalStorage } from '../javascript/loggedin.js'
import { deleteData } from '../javascript/api.js'
import { goBack } from '../javascript/goToPrevious'

export default {
    created() {
        clearLocalStorage()
        deleteData('users', 'logout').then(res => {
            console.log(res)
        }).catch(err => {
            console.log(err)
        })
        goBack(this.$router)
        return
    }
}

</script>
