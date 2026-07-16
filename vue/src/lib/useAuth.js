import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useUserStore } from '@/stores/user'

export function useAuth() {
    const userStore = useUserStore()
    const { user, isLoggedIn } = storeToRefs(userStore)

    onMounted(() => {
        userStore.fetchUser()
    })

    return {
        user,
        isAuthenticated: isLoggedIn,
        fetchUser: () => userStore.fetchUser(true),
    }
}
