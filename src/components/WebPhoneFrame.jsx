export default function WebPhoneFrame({ children }) {
  return (
    <div className="web-phone-page">
      <div className="web-phone-frame">
        <div className="web-phone-notch" aria-hidden="true" />
        <div className="web-phone-screen">{children}</div>
      </div>
    </div>
  );
}
