<script setup>
import { useUserStore } from '@/stores/user'
import { useRouter } from 'vue-router'

const userStore = useUserStore()
const router = useRouter()

userStore.clearUser()
router.push('/login') // 또는 원하는 경로
</script>
