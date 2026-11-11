export interface Hotel {
  id: number;
  name: string;
  description: string;
  city: string;
  price_per_night: number;
  rating: string;
  image: string;
}
export interface Booking {
  id: number;
  hotel_name: string;
  guest_name: string;
  guest_email: string;
  check_in: string;
  check_out: string;
  guests: number;
  total_price: string;
  status: string;
}
