import { Redirect } from 'expo-router';

export default function Index() {
    // Logic untuk cek session bisa ditaruh di sini nanti
    // Untuk sekarang langsung ke login
    return <Redirect href="/(auth)/login" />;
}
