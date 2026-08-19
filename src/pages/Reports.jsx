import Hisab from './Hisab.jsx';
import StatementReport from './StatementReport.jsx';

export default function Reports({ initialView = 'hisab' }) {
  if (initialView === 'statement') {
    return <StatementReport />;
  }

  return <Hisab />;
}
