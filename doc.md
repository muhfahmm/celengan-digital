# Dokumentasi Teknis: Celengan Digital Mobile

## Fix Bottom Navigation Tenggelam di Android

### Masalah
Bottom tab navigation bar tertutup/tenggelam oleh tombol sistem Android (back, home, recent apps) pada beberapa device, terutama device dengan gesture navigation.

### Root Cause
Tab bar tidak memiliki padding yang cukup untuk menghindari area sistem Android (system navigation bar). Tinggi tab bar yang fixed (60px) tidak memperhitungkan ruang tambahan yang diperlukan untuk tombol sistem.

### Solusi: Safe Area Insets

#### Dependencies
Pastikan package berikut sudah terinstall:
```json
"react-native-safe-area-context": "~5.6.0"
```

#### Implementation

**File:** `mobile/app/main/_layout.tsx`

```tsx
import { Tabs } from 'expo-router';
import { Colors } from '../../constants/Colors';
import { Ionicons, FontAwesome6 } from '@expo/vector-icons';
import { useColorScheme } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

export default function TabLayout() {
    const colorScheme = useColorScheme();
    const isDark = colorScheme === 'dark';
    const insets = useSafeAreaInsets();

    return (
        <Tabs
            screenOptions={{
                headerShown: false,
                tabBarStyle: {
                    backgroundColor: isDark ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                    borderTopWidth: 0,
                    elevation: 0,
                    height: 60 + insets.bottom,
                    paddingBottom: insets.bottom > 0 ? insets.bottom : 8,
                    paddingTop: 8,
                },
                tabBarActiveTintColor: Colors.primary,
                tabBarInactiveTintColor: '#9CA3AF',
            }}
        >
            {/* Tab screens... */}
        </Tabs>
    );
}
```

#### Penjelasan Kode

##### 1. Import Hook
```tsx
import { useSafeAreaInsets } from 'react-native-safe-area-context';
```
Hook ini menyediakan informasi tentang area yang aman dari elemen sistem (notch, status bar, navigation bar).

##### 2. Gunakan Hook
```tsx
const insets = useSafeAreaInsets();
```
Object `insets` berisi:
- `insets.top` - Jarak dari atas (untuk notch/status bar)
- `insets.bottom` - Jarak dari bawah (untuk navigation bar)
- `insets.left` - Jarak dari kiri
- `insets.right` - Jarak dari kanan

##### 3. Dynamic Height
```tsx
height: 60 + insets.bottom
```
- **Base height:** 60px (tinggi normal tab bar)
- **Dynamic addition:** `insets.bottom` akan otomatis:
  - `0px` pada device tanpa gesture navigation
  - `20-40px` pada device dengan gesture bar
  - Nilai custom tergantung manufacturer device

##### 4. Dynamic Padding
```tsx
paddingBottom: insets.bottom > 0 ? insets.bottom : 8
```
- Jika ada navigation bar (`insets.bottom > 0`), gunakan nilai tersebut
- Jika tidak ada, gunakan padding default 8px
- Ini memastikan icon tidak terlalu dekat dengan navigation bar

### Hasil

| Kondisi | Sebelum | Sesudah |
|---------|---------|---------|
| Device tanpa gesture | ✅ Normal | ✅ Normal |
| Device dengan gesture bar | ❌ Tertutup | ✅ Normal |
| Device dengan navbar tinggi | ❌ Tertutup sebagian | ✅ Normal |

### Testing

Cara test implementasi:
1. Buka app di device Android dengan gesture navigation
2. Navigate antar tabs (Dashboard, Celengan, Profil)
3. Pastikan semua icon terlihat dengan jelas
4. Pastikan tidak ada icon yang tertutup navigation bar
5. Test di berbagai device/emulator dengan ukuran berbeda

### Alternative Approach (Tidak Digunakan)

#### Approach 1: Fixed Padding ❌
```tsx
paddingBottom: 20  // Tidak ideal
```
**Masalah:** Tidak fleksibel, bisa terlalu banyak di device tanpa navbar, terlalu sedikit di device dengan navbar tinggi.

#### Approach 2: Position Absolute + Manual Bottom ❌
```tsx
position: 'absolute',
bottom: 20
```
**Masalah:** Overlap dengan konten, tidak responsive terhadap keyboard.

#### Approach 3: SafeAreaView Wrapper ⚠️
Menggunakan `SafeAreaView` di parent component bisa work, tapi tidak recommended karena:
- Lebih kompleks
- Perlu modify multiple files
- Tab bar sudah punya built-in style yang lebih mudah dikustomisasi

### Best Practice

✅ **DO:**
- Gunakan `useSafeAreaInsets()` untuk dynamic spacing
- Test di berbagai device sizes
- Keep base height minimal (60px)
- Use conditional padding

❌ **DON'T:**
- Hardcode padding values
- Use `position: absolute` without calculating bottom insets
- Ignore safe area di tab navigation

---

## Related Files

- [`mobile/app/main/_layout.tsx`](file:///c:/xampp/htdocs/celengan-digital/mobile/app/main/_layout.tsx) - Tab navigation layout
- [`mobile/package.json`](file:///c:/xampp/htdocs/celengan-digital/mobile/package.json) - Dependencies

## References

- [React Native Safe Area Context Documentation](https://github.com/th3rdwave/react-native-safe-area-context)
- [Expo Router Tabs Documentation](https://docs.expo.dev/router/advanced/tabs/)
